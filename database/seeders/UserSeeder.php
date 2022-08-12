<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Constants\UserRole;

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
            'firstname' => 'Admin',
            'lastname' => 'Test',
            'email' => 'admin.test@heig-vd.ch',
            'role' => UserRole::ADMIN,
            'orientation_id' => 1
        ]);
        User::create([
            'firstname' => 'Professor1',
            'lastname' => 'Test',
            'email' => 'professor1.test@heig-vd.ch',
            'role' => UserRole::PROFESSOR,
            'orientation_id' => 1
        ]);
        User::create([
            'firstname' => 'Professor2',
            'lastname' => 'Test',
            'email' => 'professor2.test@heig-vd.ch',
            'role' => UserRole::PROFESSOR,
            'orientation_id' => 1
        ]);
        User::create([
            'firstname' => 'Student1',
            'lastname' => 'Test',
            'email' => 'student1.test@heig-vd.ch',
            'role' => UserRole::STUDENT,
            'orientation_id' => 1
        ]);
        User::create([
            'firstname' => 'Student2',
            'lastname' => 'Test',
            'email' => 'student2.test@heig-vd.ch',
            'role' => UserRole::STUDENT,
            'orientation_id' => 1
        ]);
    }
}
