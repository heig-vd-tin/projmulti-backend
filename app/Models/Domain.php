<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /** Orientations qui couvrent ce domaine ; pivot.enlarged = seulement en élargissant. */
    public function orientations()
    {
        return $this->belongsToMany(Orientation::class, 'domain_orientation')->withPivot('enlarged');
    }
}
