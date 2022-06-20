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
    }
}
