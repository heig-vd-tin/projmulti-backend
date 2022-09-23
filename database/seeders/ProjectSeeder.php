<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    private $count = 2; // number of projects by prof
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nbr_prof = User::where('role', 'like', 'professor')->count();
        $profs = User::where('role', 'like', 'professor')->get();

        $nbr_orientations = Orientation::count();

        $cpt_proj = 0;
        foreach ($profs as $prof) {
            for ($i = 0; $i < $this->count; $i++) {
                $project = Project::firstOrNew([
                    'title' => 'Project ' . $cpt_proj,
                    'description' => 'Description of project ' . $cpt_proj,
                    'owner_id' => $prof->id,
                ]);
                $project->save();
                $cpt_proj++;
            }
        }

        for ($i = 1; $i <= $this->count; $i++) {
            $project = Project::create([
                'title' => 'Project' . $i,
                'description' => 'Ceci est une description...',
                'owner_id' => 1
            ]);

            $project->orientations()->attach([
                1 => ['importance' => rand(1, 3)],
                2 => ['importance' => rand(1, 3)],
                3 => ['importance' => rand(1, 3)]
            ]);
        }
    }
}
