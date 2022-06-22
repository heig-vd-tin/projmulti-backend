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
}
