<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['name' => 'Admin'],
            ['name' => 'Professor'],
            ['name' => 'Assistant'],
            ['name' => 'Student'],
            ['name' => 'Trainee'],
        ];
        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
