<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'firstname' => 'Tristan',
            'lastname' => 'Lieberherr',
            'email' => 'tristan.lieberherr@heig-vd.ch',
            'role_id' => 1,
            'orientation_id' => 1,
        ]);
        User::create([
            'firstname' => 'Yves',
            'lastname' => 'Chevallier',
            'email' => 'yves.chevallier@heig-vd.ch',
            'role_id' => 2,
            'orientation_id' => 2,
        ]);
        User::create([
            'firstname' => 'Tony',
            'lastname' => 'Maulaz',
            'email' => 'tony.maulaz@heig-vd.ch',
            'role_id' => 2,
            'orientation_id' => 1,
        ]);
        User::create([
            'firstname' => 'Kevin',
            'lastname' => 'Nikev',
            'email' => 'kevin.nikev@heig-vd.ch',
            'role_id' => 4,
            'orientation_id' => 3,
        ]);
    }
}
