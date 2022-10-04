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

class StdSeeder2022 extends Seeder
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

        $file = file('./public/pref2022.csv');
        $data = [];
        foreach ($file as $line) {
            $data = str_getcsv($line, ';');
            $firstname = explode(' ', $data[2])[0];
            
            if( isset( explode(' ', $data[2])[1] )) 
                $lastname = explode(' ', $data[2])[1];
            else
                $lastname = 'noname';

            $prj_ref = $data[1]; // TMZ1
            $pref = explode(' ', $data[0])[1];
            $or = $data[3];

            $u = User::where('firstname', $firstname)->first();
            if( !isset($u) ){
                $u = User::create([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $faker->unique()->safeEmail(),
                    'initials' => '',
                    'orientation_id' => $or,
                    'role' => UserRole::STUDENT
                ]);
            }

            $project = Project::where('reference', 'like', $prj_ref)->first();
            if( isset($project) ){
                $preference = Preference::firstOrNew([
                    'project_id' => $project->id,
                    'user_id' => $u->id,
                ]);
                $preference->priority = $pref;
                $preference->save();
            }
            else{
                dd($prj_ref);
            }
        }


    }
}
