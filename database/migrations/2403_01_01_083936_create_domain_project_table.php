<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_project', function (Blueprint $table) {
            $table->id();
            $table->integer('importance')->default(1);
            $table->unsignedBigInteger('domain_id');
            $table->unsignedBigInteger('project_id');

            $table->unique(['domain_id', 'project_id']);

            $table->foreign('domain_id')->references('id')->on('domains');
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
        Schema::dropIfExists('domain_project');
    }
}
