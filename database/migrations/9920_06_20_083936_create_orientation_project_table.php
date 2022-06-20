<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrientationProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orientation_project', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orientation_id');
            $table->unsignedBigInteger('project_id');

            $table->foreign('orientation_id')->references('id')->on('orientations');
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orientation_project');
    }
}
