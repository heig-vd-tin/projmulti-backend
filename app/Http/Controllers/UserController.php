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

    public function getUnassigned()
    {
        return User::doesntHave('assignments')->where('role', UserRole::STUDENT)->orderBy('lastname')->get();
    }
}
