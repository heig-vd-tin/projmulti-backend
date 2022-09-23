<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\Domain;

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

        $nbr_domains = Domain::count();

        $cpt_proj = 0;
        foreach ($profs as $prof) {
            for ($i = 0; $i < $this->count; $i++) {
                $project = Project::create([
                    'title' => 'Project ' . $cpt_proj,
                    'description' => 'Description of project ' . $cpt_proj,
                    'owner_id' => $prof->id,
                ]);

                $nbr = rand(3, $nbr_domains);
                $domains = Domain::all()->random($nbr);
                
                foreach ($domains as $d) {
                    $project->domains()->attach($d->id, ['importance' => rand(1, 3)]);
                }

                $project->save();
                $cpt_proj++;
            }
        }
    }
}
