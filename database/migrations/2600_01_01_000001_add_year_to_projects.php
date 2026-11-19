<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Année du cours à laquelle appartient le projet (ex. 2026). Les projets sont conservés
 * d'une année à l'autre au lieu de vider la base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable()->after('owner_id')->index();
        });

        // Projets existants : un projet saisi d'août à décembre est pour le cours de l'année suivante
        // (saisie déc. 2025 / janv. 2026 → cours 2026).
        DB::table('projects')->update([
            'year' => DB::raw('IF(MONTH(created_at) >= 8, YEAR(created_at) + 1, YEAR(created_at))'),
        ]);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};
