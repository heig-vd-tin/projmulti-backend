<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Constants\UserRole;
use Illuminate\Database\Eloquent\Factories\Sequence;

use App\Models\Orientation;

class UserSeeder extends Seeder
{
    private $count = 50;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Keycloak bound users
        /*
        User::create([
            'firstname' => 'Admin',
            'lastname' => 'Test',
            'email' => 'admin.test@heig-vd.ch',
            'role' => UserRole::ADMIN
        ]);
        User::create([
            'firstname' => 'Professor1',
            'lastname' => 'Test',
            'email' => 'professor1.test@heig-vd.ch',
            'role' => UserRole::PROFESSOR
        ]);
        User::create([
            'firstname' => 'Professor2',
            'lastname' => 'Test',
            'email' => 'professor2.test@heig-vd.ch',
            'role' => UserRole::PROFESSOR
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
        ]);*/
        
        //Fake users
        //User::factory()->count($this->count)->create();

        //Fake users admin
        User::factory()
            ->count(2)
            ->create([
                'role' => UserRole::ADMIN,
                'orientation_id' => null
        ]);

        //Fake users professor
        User::factory()
            ->count(10)
            ->create([
                'role' => UserRole::PROFESSOR,
                'orientation_id' => null
        ]);

        // can be use to create fake users with a specific orientation
        $nbr_orientation = Orientation::count();

        //Fake users student
        User::factory()
            ->count(70)
            ->state(new Sequence(
                fn ($sequence) => [
                    'orientation_id' => Orientation::all()->random(),
                    'role' => UserRole::STUDENT
                ],
            ))
            ->create();        
    }
}
