<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /** L'admin peut tout faire, quelle que soit la phase. */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Project $project): bool
    {
        // Brouillon : visible seulement par son responsable.
        if (!$project->proposed && $user->id !== $project->owner_id) {
            return false;
        }
        // Les étudiants ne voient que les projets de l'année en cours.
        return !$user->isStudent() || $project->isCurrentYear();
    }

    public function createProject(User $user): Response
    {
        if (!$user->isTeacher()) {
            return Response::deny('Seuls les enseignants peuvent proposer un projet.');
        }
        if (Setting::phase() !== Setting::PHASE_PROJECTS) {
            return Response::deny('La saisie des projets est fermée.');
        }
        return Response::allow();
    }

    public function editProject(User $user, Project $project): Response
    {
        if ($user->id !== $project->owner_id) {
            return Response::deny("Vous n'êtes pas le responsable de ce projet.");
        }
        if (!$project->isCurrentYear()) {
            return Response::deny("Ce projet appartient à une année précédente. Dupliquez-le pour le reprendre cette année.");
        }
        if (Setting::phase() !== Setting::PHASE_PROJECTS) {
            return Response::deny('La saisie des projets est fermée.');
        }
        return Response::allow();
    }

    /** Reprendre un ancien projet dans l'année en cours. */
    public function duplicate(User $user, Project $project): Response
    {
        if ($user->id !== $project->owner_id) {
            return Response::deny("Vous n'êtes pas le responsable de ce projet.");
        }
        return $this->createProject($user);
    }
}
