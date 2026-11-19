<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Réglages modifiables en ligne par l'administrateur (phase ouverte, année courante).
 */
class Setting extends Model
{
    public const PHASE = 'phase';
    public const CURRENT_YEAR = 'current_year';

    /** Saisie des projets par les profs. */
    public const PHASE_PROJECTS = 'projects';
    /** Choix des projets par les étudiants. */
    public const PHASE_PREFERENCES = 'preferences';
    /** Plateforme fermée (répartition en cours) : seul l'admin peut agir. */
    public const PHASE_CLOSED = 'closed';

    public const PHASES = [self::PHASE_PROJECTS, self::PHASE_PREFERENCES, self::PHASE_CLOSED];

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, self::$cache)) {
            self::$cache[$key] = static::query()->find($key)?->value;
        }
        return self::$cache[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        self::$cache[$key] = (string) $value;
    }

    public static function phase(): string
    {
        return self::get(self::PHASE, self::PHASE_CLOSED);
    }

    /** Année du cours en préparation (ex. 2027). */
    public static function currentYear(): int
    {
        $default = (int) date('n') >= 8 ? (int) date('Y') + 1 : (int) date('Y');
        return (int) self::get(self::CURRENT_YEAR, $default);
    }
}
