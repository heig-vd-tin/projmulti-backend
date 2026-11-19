<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'domain_id',
        'level',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
