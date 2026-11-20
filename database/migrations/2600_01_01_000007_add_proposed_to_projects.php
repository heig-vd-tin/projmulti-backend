<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Projet proposé aux étudiants (true) ou brouillon (false) : un brouillon n'est visible que
 * par son responsable et l'admin, et n'entre ni dans les choix ni dans la sélection.
 * Les projets existants restent proposés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('proposed')->default(true)->after('selected');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('proposed');
        });
    }
};
