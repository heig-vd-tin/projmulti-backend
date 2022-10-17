<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title', 100);
            $table->string('reference', 100)->nullable();
            $table->string('short_description', 255);
            $table->mediumText('description')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->boolean('is_active')->default(false);
            $table->integer('score')->default(0);
            $table->boolean('miss_student')->default(true);
            $table->boolean('selected')->default(false);

            $table->integer('nb_student')->default(0); // tmz : for debub
            $table->integer('nb_domain')->default(0); // tmz : for debub

            $table->foreign('owner_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
