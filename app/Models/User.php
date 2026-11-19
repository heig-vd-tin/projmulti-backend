<?php

namespace App\Models;

use App\Constants\UserRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'role',
        'orientation_id',
        'password',
        'initials',
        'excluded',
        'locked',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // Étudiant retiré de la répartition (sujet personnel, abandon).
        'excluded' => 'boolean',
        // Affectation figée : la répartition automatique ne la change pas.
        'locked' => 'boolean',
    ];

    protected $with = [
        'orientation',
    ];

    public function orientation()
    {
        return $this->belongsTo(Orientation::class);
    }

    public function preferences()
    {
        return $this->hasMany(Preference::class)->orderby('priority');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function match_projects()
    {
        return $this->belongsToMany(Project::class, 'matches');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function isAdmin()
    {
        return $this->role == UserRole::ADMIN;
    }

    public function isProfessor()
    {
        return $this->role == UserRole::PROFESSOR;
    }

    public function isStudent()
    {
        return $this->role == UserRole::STUDENT;
    }

    public function isTeacher()
    {
        return in_array($this->role, UserRole::TEACHERS);
    }
}
