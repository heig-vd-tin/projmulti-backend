<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** Colonnes renvoyées sans la description (qui peut peser plusieurs Mo à cause des images). */
    public const LIGHT_COLUMNS = [
        'id', 'created_at', 'updated_at', 'title', 'reference', 'short_description', 'owner_id', 'year',
        'is_active', 'score', 'miss_student', 'selected', 'nb_student', 'nb_domain', 'state',
    ];

    protected $fillable = [
        'title',
        'description',
        'short_description',
        'owner_id',
        'miss_student',
        'score',
        'state',
        'year',
    ];

    protected $casts = [
        'year' => 'integer',
        'state' => 'integer',
        'selected' => 'boolean',
        'is_active' => 'boolean',
        'miss_student' => 'boolean',
    ];

    /**
     * Relations toujours chargées. Les préférences et affectations ne sont plus chargées
     * par défaut : elles exposaient aux étudiants les choix des autres étudiants.
     */
    protected $with = [
        'owner',
        'domains',
        'tags',
    ];

    public function scopeOfYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeCurrentYear(Builder $query): Builder
    {
        return $query->where('year', Setting::currentYear());
    }

    public function isCurrentYear(): bool
    {
        return $this->year === Setting::currentYear();
    }

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
