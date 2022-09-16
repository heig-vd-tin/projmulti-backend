<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    private $count = 10;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
