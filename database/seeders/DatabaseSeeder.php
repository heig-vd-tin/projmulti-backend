<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\OrientationSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\TagSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\PreferenceSeeder;

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
            TagSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            PreferenceSeeder::class
        ]);
    }
}
