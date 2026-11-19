<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orientation extends Model
{
    protected $fillable = [
        'acronym',
        'faculty_acronym',
        'department_acronym',
        'name',
        'faculty_name',
        'department_name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
