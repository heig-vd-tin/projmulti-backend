<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Project;
use App\Models\User;

/**
 * Verrous de la répartition. Un projet verrouillé a une équipe figée ; un étudiant verrouillé garde
 * son projet. Ni l'affectation manuelle (glisser-déposer, fiche), ni l'exclusion, ni le chargement
 * d'un fichier, ni le calcul automatique ne les modifient : il faut d'abord les déverrouiller.
 */
class AssignmentLocks
{
    public static function projectMessage(Project $project): string
    {
        return "Le projet « {$project->title} » est verrouillé : déverrouillez-le pour modifier son équipe.";
    }

    /** Raison pour laquelle l'affectation actuelle de l'étudiant ne peut pas changer, ou null. */
    public static function studentReason(User $student): ?string
    {
        $current = $student->assignments()->with('project:id,title,state')->first();
        if (!$current) {
            return null;
        }
        if ($student->locked) {
            return "L'affectation de {$student->firstname} {$student->lastname} est verrouillée : déverrouillez-la d'abord.";
        }
        if ($current->project && $current->project->state == 1) {
            return self::projectMessage($current->project);
        }
        return null;
    }

    /** Identifiants des étudiants dont l'affectation est figée (étudiant ou projet verrouillé). */
    public static function frozenStudentIds(): array
    {
        return Assignment::query()
            ->join('users', 'users.id', '=', 'assignments.user_id')
            ->join('projects', 'projects.id', '=', 'assignments.project_id')
            ->where(fn ($q) => $q->where('users.locked', true)->orWhere('projects.state', 1))
            ->pluck('assignments.user_id')->all();
    }

    public static function lockedProjectIds(): array
    {
        return Project::currentYear()->where('state', 1)->pluck('id')->all();
    }
}
