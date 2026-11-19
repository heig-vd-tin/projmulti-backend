<?php

namespace App\Http\Controllers;

use App\Constants\UserRole;
use App\Models\User;
use App\Services\AssignmentLocks;
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
        if (!empty($data['excluded']) && !$user->excluded && ($reason = AssignmentLocks::studentReason($user))) {
            // Exclure retirerait l'étudiant d'une équipe figée.
            return response()->json(['message' => $reason], 422);
        }
        if (!empty($data['locked']) && !$user->assignments()->exists()) {
            return response()->json(['message' => "{$user->firstname} {$user->lastname} n'est pas affecté : affectez-le avant de verrouiller."], 422);
        }
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
