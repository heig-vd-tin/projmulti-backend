<?php

namespace App\Services;

use App\Constants\UserRole;
use App\Models\Assignment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Répartition optimale des étudiants (programmation linéaire en nombres entiers, solveur CBC).
 *
 * Règles (confirmées par le responsable du cours) :
 *  - chaque étudiant (non exclu) dans un seul projet ouvert, groupes de min_size à max_size ;
 *  - au plus max_same_orientation étudiants d'une même orientation par projet, en répartissant au mieux ;
 *  - au moins un projet ouvert par enseignant ; les projets sélectionnés à la main restent ouverts ;
 *  - étudiants verrouillés et projets verrouillés : affectations actuelles conservées ;
 *  - les préférences priment sur la couverture des domaines ; hors choix seulement en dernier recours.
 */
class AssignmentSolver
{
    /** Coût d'un rang de préférence (au carré : protège les étudiants les moins bien servis). */
    private const RANK_COST = [1 => 1, 2 => 4, 3 => 9, 4 => 16, 5 => 25];
    private const OUT_OF_CHOICE_COST = 60;
    /** Étudiant en trop d'une même orientation dans un projet (répartir au mieux). */
    private const DIVERSITY_WEIGHT = 4;
    /** Bonus par domaine du projet couvert par un étudiant d'une orientation adaptée. */
    private const COVERAGE_WEIGHT = 2;
    private const PROF_WITHOUT_PROJECT_COST = 1000;
    private const UNASSIGNED_COST = 100000;
    public const LEVEL = 20;

    private string $status = 'optimal';

    public function __construct(private array $options)
    {
        $this->options += [
            'min_size' => 4,
            'max_size' => 5,
            'max_same_orientation' => 3,
            'allow_fifth_choice' => true,
            'allow_out_of_choice' => true,
            'time_limit' => 30,
        ];
    }

    /** Calcule et enregistre la répartition ; renvoie le résultat détaillé. */
    public function solve(): array
    {
        $o = $this->options;
        $projects = Project::currentYear()->with('domains')->get()->keyBy('id');
        $students = User::where('role', UserRole::STUDENT)->where('excluded', false)
            ->with(['preferences', 'assignments'])->get()->keyBy('id');
        if ($projects->isEmpty() || $students->isEmpty()) {
            throw new RuntimeException("Il n'y a pas de projets ou pas d'étudiants pour le cours en cours.");
        }

        $coverage = DB::table('domain_orientation')->where('enlarged', false)->get()
            ->groupBy('domain_id')->map(fn ($rows) => $rows->pluck('orientation_id')->all());

        // Affectations imposées : étudiants verrouillés, étudiants des projets verrouillés.
        $fixed = [];
        $lockedProjects = $projects->filter(fn ($p) => $p->state == 1)->keys()->all();
        foreach ($students as $s) {
            $current = $s->assignments->first()?->project_id;
            if ($current && $projects->has($current) && ($s->locked || in_array($current, $lockedProjects))) {
                $fixed[$s->id] = $current;
            }
        }

        // Variables x_s_p : uniquement les couples possibles.
        $pairs = [];
        $maxRank = $o['allow_fifth_choice'] ? 5 : 4;
        foreach ($students as $s) {
            $ranks = $s->preferences->pluck('priority', 'project_id');
            foreach ($projects as $p) {
                if (isset($fixed[$s->id])) {
                    if ($fixed[$s->id] === $p->id) {
                        $pairs[$s->id][$p->id] = $this->cost($ranks[$p->id] ?? null);
                    }
                    continue;
                }
                if (in_array($p->id, $lockedProjects)) {
                    continue; // l'équipe d'un projet verrouillé est figée
                }
                $rank = $ranks[$p->id] ?? null;
                if ($rank !== null && $rank <= $maxRank) {
                    $pairs[$s->id][$p->id] = self::RANK_COST[$rank] ?? self::OUT_OF_CHOICE_COST;
                } elseif ($o['allow_out_of_choice']) {
                    $pairs[$s->id][$p->id] = self::OUT_OF_CHOICE_COST;
                }
            }
        }

        $obj = [];
        $cons = [];
        $bin = [];
        $gen = [];

        foreach ($students as $s) {
            $u = "u_{$s->id}";
            $bin[] = $u;
            $obj[] = self::UNASSIGNED_COST . " $u";
            $terms = [$u];
            foreach ($pairs[$s->id] ?? [] as $pid => $cost) {
                $x = "x_{$s->id}_{$pid}";
                $bin[] = $x;
                $obj[] = "$cost $x";
                $terms[] = $x;
                $cons[] = "$x - y_$pid <= 0";
            }
            $cons[] = implode(' + ', $terms) . ' = 1';
            if (isset($fixed[$s->id])) {
                $cons[] = "x_{$s->id}_{$fixed[$s->id]} = 1";
            }
        }

        foreach ($projects as $p) {
            $y = "y_{$p->id}";
            $bin[] = $y;
            $members = [];
            foreach ($students as $s) {
                if (isset($pairs[$s->id][$p->id])) {
                    $members[$s->id] = "x_{$s->id}_{$p->id}";
                }
            }
            $sum = $members ? implode(' + ', $members) : null;
            $locked = in_array($p->id, $lockedProjects);

            if ($locked) {
                // Projet verrouillé : ouvert s'il a déjà des étudiants, taille libre.
                $cons[] = $sum ? "$y - " . implode(' - ', $members) . ' <= 0' : "$y = 0";
            } elseif ($sum) {
                $cons[] = "$sum - {$o['max_size']} $y <= 0";
                $cons[] = "$sum - {$o['min_size']} $y >= 0";
            } else {
                $cons[] = "$y = 0";
            }
            if ($p->selected && !$locked) {
                $cons[] = "$y = 1";
            }

            // Orientations : plafond strict et pénalité au-delà d'un étudiant.
            $byOrientation = [];
            foreach ($members as $sid => $x) {
                $byOrientation[$students[$sid]->orientation_id ?? 0][] = $x;
            }
            foreach ($byOrientation as $oid => $xs) {
                if ($oid === 0) {
                    continue;
                }
                $list = implode(' + ', $xs);
                if (!$locked) {
                    $cons[] = "$list <= {$o['max_same_orientation']}";
                }
                if (count($xs) > 1) {
                    $e = "e_{$p->id}_{$oid}";
                    $gen[] = $e;
                    $obj[] = self::DIVERSITY_WEIGHT . " $e";
                    $cons[] = "$list - $e <= 1";
                }
            }

            // Couverture des domaines du projet.
            foreach ($p->domains as $d) {
                $covering = array_filter($members, fn ($x, $sid) => in_array($students[$sid]->orientation_id, $coverage[$d->id] ?? []), ARRAY_FILTER_USE_BOTH);
                if (!$covering) {
                    continue;
                }
                $c = "c_{$p->id}_{$d->id}";
                $bin[] = $c;
                $obj[] = '- ' . self::COVERAGE_WEIGHT . " $c";
                $cons[] = "$c - " . implode(' - ', $covering) . ' <= 0';
            }
        }

        // Au moins un projet ouvert par enseignant (pénalité très forte si impossible).
        foreach ($projects->groupBy('owner_id') as $owner => $owned) {
            $v = "v_$owner";
            $bin[] = $v;
            $obj[] = self::PROF_WITHOUT_PROJECT_COST . " $v";
            $cons[] = implode(' + ', $owned->map(fn ($p) => "y_{$p->id}")->all()) . " + $v >= 1";
        }

        $values = $this->runCbc($obj, $cons, $bin, $gen);

        return DB::transaction(fn () => $this->store($values, $students, $projects, $coverage, $fixed));
    }

    private function cost(?int $rank): int
    {
        return $rank ? (self::RANK_COST[$rank] ?? self::OUT_OF_CHOICE_COST) : self::OUT_OF_CHOICE_COST;
    }

    private function runCbc(array $obj, array $cons, array $bin, array $gen): array
    {
        $dir = storage_path('app/solver/' . uniqid('run_', true));
        @mkdir($dir, 0775, true);
        $lp = "$dir/model.lp";
        $sol = "$dir/solution.txt";

        $text = "Minimize\n obj: " . implode(" + ", $obj) . "\nSubject To\n";
        foreach ($cons as $i => $c) {
            $text .= " c$i: $c\n";
        }
        if ($gen) {
            $text .= "Bounds\n" . implode('', array_map(fn ($g) => " $g >= 0\n", $gen));
        }
        $text .= "Binaries\n " . implode("\n ", array_unique($bin)) . "\nEnd\n";
        $text = str_replace('+ -', '-', $text);
        file_put_contents($lp, $text);

        $process = new Process(['cbc', $lp, '-sec', (string) $this->options['time_limit'], '-solve', '-solu', $sol]);
        $process->setTimeout($this->options['time_limit'] + 30);
        $process->run();

        $content = is_file($sol) ? file_get_contents($sol) : '';
        array_map('unlink', glob("$dir/*"));
        @rmdir($dir);

        $first = strtok($content, "\n") ?: '';
        if (!$process->isSuccessful() || $content === '' || stripos($first, 'infeasible') !== false) {
            Log::error('Solveur CBC', ['sortie' => substr($process->getOutput() . $process->getErrorOutput(), -2000), 'statut' => $first]);
            throw new RuntimeException('Aucune répartition possible avec ces paramètres (taille des groupes, projets sélectionnés ou verrouillés).');
        }
        $this->status = str_starts_with(trim($first), 'Optimal') ? 'optimal' : 'feasible';

        $values = [];
        foreach (explode("\n", $content) as $line) {
            if (preg_match('/^\s*\*{0,2}\s*\d+\s+(\S+)\s+(-?[\d.e+-]+)/', $line, $m)) {
                $values[$m[1]] = (float) $m[2];
            }
        }
        return $values;
    }

    private function store(array $values, $students, $projects, $coverage, array $fixed): array
    {
        // Remplace les affectations, sauf celles qui étaient imposées (identiques de toute façon).
        Assignment::whereIn('user_id', $students->keys())->delete();
        Assignment::whereIn('user_id', User::where('excluded', true)->pluck('id'))->delete();

        $assignments = [];
        $byRank = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0, 'out' => 0];
        $outOfChoice = [];
        $unassigned = [];
        $sizes = [];
        $rankSum = 0;

        foreach ($students as $s) {
            $pid = null;
            foreach ($projects as $p) {
                if (($values["x_{$s->id}_{$p->id}"] ?? 0) > 0.5) {
                    $pid = $p->id;
                    break;
                }
            }
            if (!$pid) {
                $unassigned[] = $s->id;
                continue;
            }
            $project = $projects[$pid];
            $domain = $project->domains->first(fn ($d) => in_array($s->orientation_id, $coverage[$d->id] ?? []));
            $assignments[] = Assignment::create([
                'project_id' => $pid,
                'user_id' => $s->id,
                'domain_id' => $domain?->id,
                'level' => self::LEVEL,
            ]);
            $rank = $s->preferences->firstWhere('project_id', $pid)?->priority;
            if ($rank) {
                $byRank[(string) $rank]++;
                $rankSum += $rank;
            } else {
                $byRank['out']++;
                $outOfChoice[] = $s->id;
            }
            $sizes[$pid] = ($sizes[$pid] ?? 0) + 1;
        }

        $groupSizes = [];
        foreach ($sizes as $n) {
            $groupSizes[(string) $n] = ($groupSizes[(string) $n] ?? 0) + 1;
        }
        ksort($groupSizes);
        $ranked = count($assignments) - count($outOfChoice);

        return [
            'status' => $this->status,
            'assignments' => collect($assignments)->map->only(['id', 'project_id', 'user_id', 'domain_id', 'level'])->values(),
            'metrics' => [
                'students' => $students->count(),
                'assigned' => count($assignments),
                'unassigned' => count($unassigned),
                'by_rank' => $byRank,
                'mean_rank' => $ranked ? round($rankSum / $ranked, 2) : null,
                'open_projects' => count($sizes),
                'group_sizes' => $groupSizes,
            ],
            'out_of_choice' => $outOfChoice,
            'unassigned' => $unassigned,
        ];
    }
}
