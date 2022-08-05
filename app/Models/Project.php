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

    protected $with = ['orientations', 'tags', 'attributed_users'];

    public function orientations()
    {
        return $this->belongsToMany(Orientation::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function preferences()
    {
        return $this->hasMany(Preference::class);
    }

    public function attributed_users()
    {
        return $this->belongsToMany(User::class, 'attributions');
    }
}
