<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function createProject(User $user)
    {
        return $user->isTeacher();
    }

    public function editProject(User $user, Project $project)
    {
        return $user->id === $project->owner_id;
    }
}
