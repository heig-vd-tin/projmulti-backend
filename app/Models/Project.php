<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'short_description',
        'owner_id',
        'miss_student',
        'score',
        'state'
    ];

    protected $with = [
        'owner',
        'domains',
        'tags',
        'assigned_users'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function domains()
    {
        return $this->belongsToMany(Domain::class)->withPivot('importance')->orderByDesc('importance');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function preferences()
    {
        return $this->hasMany(Preference::class);
    }

    public function assigned_users()
    {
        return $this->belongsToMany(User::class, 'assignments')->withPivot(['domain_id']);
    }

    public function matched_users()
    {
        return $this->belongsToMany(User::class, 'matches')->withPivot(['priority', 'version']);
    }

    public function preferred_users()
    {
        return $this->belongsToMany(User::class, 'preferences')->withPivot('priority');
    }
}
