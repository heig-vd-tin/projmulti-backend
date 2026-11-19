<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Données de référence pour une base neuve (orientations, domaines, tags). */
    public function run(): void
    {
        $this->call([
            OrientationSeeder::class,
            DomainSeeder::class,
            TagSeeder::class,
            DomainOrientationSeeder::class,
        ]);
    }
}
