<?php

namespace App\Policies;

use App\Constants\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function createProject(User $user)
    {
        return $user->canCreate();
    }
}
