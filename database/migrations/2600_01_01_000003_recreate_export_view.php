<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Vue utilisée pour l'export CSV de la répartition : ajoute l'e-mail de l'étudiant
 * (demande du responsable) et garde les étudiants sans orientation connue.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS export_data');
        DB::statement("
            CREATE VIEW export_data AS
            SELECT assign.id,
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
        DB::statement('DROP VIEW IF EXISTS export_data');
    }
};
