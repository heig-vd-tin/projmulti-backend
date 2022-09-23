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
        'owner_id',
    ];

    protected $with = [
        'domains',
        'tags'
    ];

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
        return $this->belongsToMany(User::class, 'assignments');
    }

    public function preferred_users()
    {
        return $this->belongsToMany(User::class, 'preferences');
    }
}
