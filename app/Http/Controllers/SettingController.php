<?php

namespace App\Http\Controllers;

use App\Constants\UserRole;
use App\Models\Assignment;
use App\Models\Domain;
use App\Models\Preference;
use App\Models\Project;
use App\Models\ProjectMatch;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function show()
    {
        $current = Setting::currentYear();
        $years = Project::query()->distinct()->pluck('year')->filter()->push($current)->unique()->sortDesc()->values();

        return [
            'phase' => Setting::phase(),
            'current_year' => $current,
            'years' => $years,
        ];
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'phase' => ['sometimes', Rule::in(Setting::PHASES)],
        ]);
        if (isset($data['phase'])) {
            Setting::put(Setting::PHASE, $data['phase']);
            Log::info('Phase modifiée', ['phase' => $data['phase'], 'par' => $request->user()->email]);
        }
        return $this->show();
    }

    /**
     * Démarre une nouvelle année : supprime les choix, la répartition et les étudiants,
     * garde les projets (consultables en filtrant par année) et les enseignants.
     */
    public function newYear(Request $request)
    {
        $current = Setting::currentYear();
        $data = $request->validate([
            'year' => ['required', 'integer', 'gt:' . $current, 'max:' . ($current + 5)],
            'confirm' => ['required', 'in:NOUVELLE ANNÉE,NOUVELLE ANNEE'],
        ], [
            'year.gt' => "La nouvelle année doit être après l'année courante ($current).",
            'confirm.in' => 'Tapez NOUVELLE ANNÉE pour confirmer.',
        ]);

        DB::transaction(function () use ($data) {
            ProjectMatch::query()->delete();
            Assignment::query()->delete();
            Preference::query()->delete();
            User::where('role', UserRole::STUDENT)->delete();
            // Les indicateurs de répartition ne concernent que l'année écoulée.
            Project::query()->update([
                'selected' => false, 'score' => 0, 'nb_student' => 0, 'nb_domain' => 0,
                'miss_student' => true, 'state' => 0,
            ]);
            Setting::put(Setting::CURRENT_YEAR, $data['year']);
            Setting::put(Setting::PHASE, Setting::PHASE_PROJECTS);
        });

        Log::warning('Nouvelle année démarrée', ['annee' => $data['year'], 'par' => $request->user()->email]);
        return $this->show();
    }

    /** Orientations qui couvrent chaque domaine (utilisé par la répartition automatique). */
    public function domainMapping()
    {
        return Domain::with('orientations')->orderBy('id')->get()->map(fn ($d) => [
            'domain_id' => $d->id,
            'name' => $d->name,
            'base' => $d->orientations->where('pivot.enlarged', false)->pluck('id')->values(),
            'enlarged' => $d->orientations->where('pivot.enlarged', true)->pluck('id')->values(),
        ]);
    }

    public function updateDomainMapping(Request $request)
    {
        $data = $request->validate([
            'mapping' => 'required|array',
            'mapping.*.domain_id' => 'required|integer|exists:domains,id',
            'mapping.*.base' => 'present|array',
            'mapping.*.base.*' => 'integer|exists:orientations,id',
            'mapping.*.enlarged' => 'present|array',
            'mapping.*.enlarged.*' => 'integer|exists:orientations,id',
        ]);
        DB::transaction(function () use ($data) {
            foreach ($data['mapping'] as $row) {
                $sync = collect($row['enlarged'])->mapWithKeys(fn ($o) => [$o => ['enlarged' => true]])
                    ->union(collect($row['base'])->mapWithKeys(fn ($o) => [$o => ['enlarged' => false]]));
                Domain::findOrFail($row['domain_id'])->orientations()->sync($sync->all());
            }
        });
        return $this->domainMapping();
    }
}
