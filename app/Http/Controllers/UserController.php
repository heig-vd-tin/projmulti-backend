<?php

namespace App\Http\Controllers;

use App\Constants\UserRole;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getMyself(Request $request)
    {
        return $request->user();
    }

    public function getAll()
    {
        return User::orderBy('lastname')->get();
    }

    public function getAllStudents()
    {
        return User::where('role', UserRole::STUDENT)->orderBy('lastname')->get()->load(['preferences', 'assignments']);
    }

    /** Exclure un étudiant de la répartition ou figer son affectation (admin). */
    public function updateFlags(Request $request, $id)
    {
        $data = $request->validate([
            'excluded' => 'sometimes|boolean',
            'locked' => 'sometimes|boolean',
        ]);
        $user = User::where('role', UserRole::STUDENT)->findOrFail($id);
        $user->fill($data);
        if ($user->excluded) {
            // Un étudiant retiré n'est plus affecté.
            $user->assignments()->delete();
            $user->locked = false;
        }
        $user->save();
        return $user->load(['preferences', 'assignments']);
    }

    public function getUnassigned()
    {
        return User::doesntHave('assignments')->where('role', UserRole::STUDENT)->orderBy('lastname')->get()->load('preferences');
    }
}
