<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\OrientationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\TagSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            OrientationSeeder::class,
            RoleSeeder::class,
            TagSeeder::class,
            UserSeeder::class,
        ]);
    }
}
