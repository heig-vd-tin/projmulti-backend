<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Vue d'export : ajoute l'id du projet (la colonne id est celle de l'affectation). */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS export_data');
        DB::statement("
            CREATE VIEW export_data AS
            SELECT assign.id,
                   prj.id AS project_id,
                   prj.title,
                   CONCAT(prof.firstname, ' ', prof.lastname) AS prof_fullname,
                   stu.id AS stu_id,
                   CONCAT(stu.firstname, ' ', stu.lastname) AS stu_fullname,
                   stu.email AS stu_email,
                   pref.priority,
                   ori.acronym AS orientation
            FROM assignments AS assign
            JOIN projects prj ON prj.id = assign.project_id
            JOIN users prof ON prof.id = prj.owner_id
            JOIN users stu ON stu.id = assign.user_id
            LEFT JOIN preferences pref ON pref.project_id = assign.project_id AND pref.user_id = assign.user_id
            LEFT JOIN orientations ori ON ori.id = stu.orientation_id
            ORDER BY prj.id
        ");
    }

    public function down(): void
    {
        // La vue précédente (sans project_id) est recréée par la migration 2600_01_01_000003.
    }
};
