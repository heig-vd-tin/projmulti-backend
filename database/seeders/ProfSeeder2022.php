<?php

namespace Database\Seeders;

use Faker\Generator;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Constants\UserRole;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Container\Container;

use App\Models\Preference;

use App\Models\Orientation;

class ProfSeeder2022 extends Seeder
{
    private $count = 50;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Container::getInstance()->make(Generator::class);

        User::create([
            'lastname' => 'Krummen',
            'firstname' => 'Mikaël',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'KRM',
            'role' => UserRole::ADMIN
        ]);

        User::create([
            'lastname' => 'Maulaz',
            'firstname' => 'Tony',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'TMZ',
            'role' => UserRole::ADMIN
        ]);

        User::create([
            'lastname' => 'Baillifard',
            'firstname' => 'Marc‐André',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'BAM',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'lastname' => 'Hochet',
            'firstname' => 'Bertrand',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'BHT',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'lastname' => 'Mentano',
            'firstname' => 'Carlo',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'CMO',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'Jean‐françois',
            'lastname' => 'Dumas',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'DJF',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'Eric',
            'lastname' => 'Boillat',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'EBA',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'François',
            'lastname' => 'Birling',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'FBG',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'Laurent',
            'lastname' => 'Gravier',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'LGN',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'Michel',
            'lastname' => 'Demierre',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'MD',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'Mokhtar',
            'lastname' => 'Bozorg',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'MRB',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'Julien',
            'lastname' => 'Ropp',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'ROJ',
            'role' => UserRole::PROFESSOR
        ]);

        User::create([
            'firstname' => 'Yves',
            'lastname' => 'Chevallier',
            'email' => $faker->unique()->safeEmail(),
            'initials' => 'YCR',
            'role' => UserRole::PROFESSOR
        ]);
    }
}
