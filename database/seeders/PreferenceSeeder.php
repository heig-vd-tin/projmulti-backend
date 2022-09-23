<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\Preference;

class PreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = User::where('role', 'like', 'student')->get();
        foreach ($students as $student) {
            
            $projects = Project::all()->random(5);

            foreach ($projects as $i=>$project) {
                $preference = Preference::firstOrNew([
                    'project_id' => $project->id,
                    'user_id' => $student->id,
                ]);
                $preference->priority = 5 - $i;
                $preference->save();
            }
        }
    }
}
