<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExportView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW export_data 
            AS
                select assign.id,
                    prj.title,
                    CONCAT(prof.firstname,' ',prof.lastname) as prof_fullname,
                    stu.id as stu_id,
                    CONCAT(stu.firstname,' ',stu.lastname) as stu_fullname,
                        priority,
                        ori.acronym as orientation

                from assignments as assign
                join projects prj on prj.id = assign.project_id
                join users prof on prof.id = prj.owner_id
                join users stu on stu.id = assign.user_id
                join preferences pref on assign.project_id = pref.project_id AND pref.user_id = assign.user_id
                join orientations ori on ori.id = stu.orientation_id
                where assign.id > 0
                order by prj.id;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
