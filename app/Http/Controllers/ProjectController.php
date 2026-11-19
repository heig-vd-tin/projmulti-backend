<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Preference;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Services\AssignmentLocks;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    private const MAX_PREFERENCES = 5;

    /** Messages de validation affichés tels quels dans le formulaire. */
    private const MESSAGES = [
        'title.required' => 'Le titre est obligatoire.',
        'title.max' => 'Le titre ne doit pas dépasser :max caractères.',
        'short_description.required' => 'Le résumé court est obligatoire.',
        'short_description.max' => 'Le résumé court ne doit pas dépasser :max caractères.',
        'description.max' => 'La description est trop volumineuse (images trop lourdes). Réduisez la taille ou le nombre des images.',
        'domains.required' => 'Sélectionnez au moins un domaine.',
        'domains.min' => 'Sélectionnez au moins un domaine.',
        'tags.max' => 'Maximum :max tags.',
    ];

    /** Année demandée (?year=), limitée à l'année courante pour les étudiants. */
    private function requestedYear(Request $request): int
    {
        if ($request->user()->isStudent() || !$request->filled('year')) {
            return Setting::currentYear();
        }
        return (int) $request->query('year');
    }

    private function withAdminRelations(Request $request, $projects)
    {
        if ($request->user()->isAdmin()) {
            return $projects->load(['preferred_users', 'assigned_users', 'matched_users']);
        }
        return $projects;
    }

    public function getAll(Request $request)
    {
        $projects = Project::ofYear($this->requestedYear($request))->get();
        return $this->withAdminRelations($request, $projects);
    }

    public function getAllWithoutDescription(Request $request)
    {
        $projects = Project::select(Project::LIGHT_COLUMNS)->ofYear($this->requestedYear($request))->get();
        return $this->withAdminRelations($request, $projects);
    }

    public function getProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('view', $project);
        // Projet complet (description, domaines, tags) : utilisé par l'affichage détaillé et l'édition.
        return [$project];
    }

    /** Projets du prof connecté, toutes années confondues (le front filtre par année). */
    public function getOwned(Request $request)
    {
        return Project::select(Project::LIGHT_COLUMNS)
            ->where('owner_id', $request->user()->id)
            ->orderByDesc('year')
            ->get()
            ->load('assigned_users');
    }

    public function getPreferred(Request $request)
    {
        // Sans la description (plusieurs Mo par projet à cause des images).
        return $request->user()->preferences()->with(['project' => fn ($q) => $q->select(Project::LIGHT_COLUMNS)])->get()
            ->filter(fn ($p) => $p->project !== null)
            ->map(function ($preference) {
                $project = $preference->project;
                $project->priority = $preference->priority;
                return $project;
            })->values();
    }

    public function getAssigned(Request $request)
    {
        return Project::find($request->user()->assignments->pluck('project_id'));
    }

    private function validateProject(Request $request, bool $editing): array
    {
        return $request->validate([
            'id' => $editing ? 'required|integer' : 'nullable',
            'title' => 'required|string|max:100',
            'short_description' => 'required|string|max:100',
            'description' => 'nullable|string|max:26214400',
            'domains' => 'required|array|min:1',
            'domains.*.id' => 'required|integer|exists:domains,id',
            'domains.*.importance' => 'required|integer',
            'tags' => 'nullable|array|max:3',
            'tags.*' => 'integer|exists:tags,id',
        ], self::MESSAGES);
    }

    private function syncRelations(Project $project, array $data): void
    {
        $domains = collect($data['domains'])->mapWithKeys(fn ($d) => [$d['id'] => ['importance' => $d['importance']]]);
        $project->domains()->sync($domains);
        $project->tags()->sync($data['tags'] ?? []);
    }

    public function createProject(Request $request)
    {
        $this->authorize('createProject', Project::class);
        $data = $this->validateProject($request, false);

        $project = DB::transaction(function () use ($request, $data) {
            $project = Project::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'short_description' => $data['short_description'],
                'owner_id' => $request->user()->id,
                'state' => 0,
                'year' => Setting::currentYear(),
            ]);
            $this->syncRelations($project, $data);
            return $project;
        });

        return $project->fresh()->load('assigned_users');
    }

    public function editProject(Request $request)
    {
        $data = $this->validateProject($request, true);
        $project = Project::findOrFail($data['id']);
        $this->authorize('editProject', $project);

        DB::transaction(function () use ($project, $data) {
            $project->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'short_description' => $data['short_description'],
            ]);
            $this->syncRelations($project, $data);
        });

        return $project->fresh()->load('assigned_users');
    }

    /** Reprend un projet d'une année précédente dans l'année en cours. */
    public function duplicateProject(Request $request, $id)
    {
        $source = Project::findOrFail($id);
        $this->authorize('duplicate', $source);
        $existing = Project::currentYear()->where('source_id', $source->id)->first();
        if ($existing) {
            return response()->json([
                'message' => "Ce projet a déjà été repris pour le cours {$existing->year}.",
                'project_id' => $existing->id,
            ], 409);
        }

        $copy = DB::transaction(function () use ($source) {
            $copy = Project::create([
                'title' => $source->title,
                'description' => $source->description,
                'short_description' => $source->short_description,
                'owner_id' => $source->owner_id,
                'state' => 0,
                'year' => Setting::currentYear(),
                'source_id' => $source->id,
            ]);
            $copy->domains()->sync($source->domains->mapWithKeys(fn ($d) => [$d->id => ['importance' => $d->pivot->importance]]));
            $copy->tags()->sync($source->tags->pluck('id'));
            return $copy;
        });

        return $copy->fresh()->load('assigned_users');
    }

    public function exportPdf(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorize('view', $project);

        $pdf = Pdf::loadView('pdf.project', ['project' => $project])
            ->setPaper('a4')
            ->setOption(['isRemoteEnabled' => false, 'isPhpEnabled' => false, 'defaultFont' => 'DejaVu Sans']);

        return $pdf->download(Str::slug($project->title) . '.pdf');
    }

    private function ensureStudent(Request $request): User
    {
        $user = $request->user();
        abort_unless($user->isStudent(), 403, 'Seuls les étudiants choisissent des projets.');
        return $user;
    }

    public function addPreference(Request $request)
    {
        $user = $this->ensureStudent($request);
        $data = $request->validate([
            'projects' => 'required|array|min:1|max:' . self::MAX_PREFERENCES,
            'projects.*.id' => ['required', 'integer', 'distinct', Rule::exists('projects', 'id')->where('year', Setting::currentYear())],
            'projects.*.priority' => 'required|integer|min:1|max:' . self::MAX_PREFERENCES,
        ]);

        DB::transaction(function () use ($user, $data) {
            foreach ($data['projects'] as $project) {
                $preference = Preference::firstOrNew(['project_id' => $project['id'], 'user_id' => $user->id]);
                $preference->priority = $project['priority'];
                $preference->save();
            }
            // Annule tout si l'étudiant dépasse le maximum de choix.
            if ($user->preferences()->count() > self::MAX_PREFERENCES) {
                throw ValidationException::withMessages(['projects' => 'Maximum ' . self::MAX_PREFERENCES . ' projets.']);
            }
        });

        return $this->getPreferred($request);
    }

    public function removePreference(Request $request)
    {
        $user = $this->ensureStudent($request);
        $request->validate(['projects' => 'required|array|min:1|max:' . self::MAX_PREFERENCES]);
        $user->preferences()->where('project_id', $request->projects[0])->delete();
        return $this->getPreferred($request);
    }

    public function selectProject(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'selected' => 'required|boolean']);
        $project = Project::findOrFail($request->id);
        $project->selected = (bool) $request->selected;
        $project->save();
        return $project->fresh();
    }

    public function lockProject(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'lock' => 'required|boolean']);
        $project = Project::findOrFail($request->id);
        $project->state = $request->lock ? 1 : 0;
        $project->save();
        return $project;
    }

    public function addAssignment(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);
        $user = User::findOrFail($data['user_id']);
        $project = Project::findOrFail($data['project_id']);
        if ($user->excluded) {
            return response()->json(['message' => "{$user->firstname} {$user->lastname} est exclu de la répartition : réintégrez-le d'abord."], 422);
        }
        if ($project->state == 1) {
            return response()->json(['message' => AssignmentLocks::projectMessage($project)], 422);
        }
        if ($reason = AssignmentLocks::studentReason($user)) {
            return response()->json(['message' => $reason], 422);
        }
        Assignment::updateOrCreate(['user_id' => $user->id], ['project_id' => $project->id]);
        return $user;
    }

    public function removeAssignment(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        $user = User::findOrFail($request->user_id);
        if ($reason = AssignmentLocks::studentReason($user)) {
            return response()->json(['message' => $reason], 422);
        }
        $user->assignments()->delete();
        return $user;
    }
}
