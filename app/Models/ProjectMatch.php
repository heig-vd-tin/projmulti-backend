<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Correspondance étudiant ↔ projet calculée pour la répartition automatique.
 * Anciennement « Match », renommé car « match » est un mot réservé depuis PHP 8.
 */
class ProjectMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'project_id',
        'user_id',
        'domain_id',
        'priority',
        'enlarge',
        'version',
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
}
