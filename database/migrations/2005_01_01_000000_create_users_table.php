<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('initials', 5)->nullable();
            $table->string('email')->unique();
            $table->string('role');
            $table->unsignedBigInteger('orientation_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->default('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('orientation_id')->references('id')->on('orientations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
