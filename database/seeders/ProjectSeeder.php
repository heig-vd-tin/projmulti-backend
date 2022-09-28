<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\Domain;
use App\Models\Tag;
use Faker\Generator;
use Illuminate\Container\Container;

class ProjectSeeder extends Seeder
{
    private $count = 2; // number of projects by prof
    private $nbr_domains = 4; // number max of domains by project
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Container::getInstance()->make(Generator::class);

        $nbr_prof = User::where('role', 'like', 'professor')->count();
        $profs = User::where('role', 'like', 'professor')->get();

        //$nbr_domains = Domain::count();

        $cpt_proj = 0;
        foreach ($profs as $prof) {
            for ($i = 0; $i < $this->count; $i++) {
                $project = Project::create([
                    'title' => 'Project ' . $cpt_proj,
                    'short_description' => $faker->realText( rand(20, 100) ),
                    'description' => $faker->realText( rand(20, 800) ), //'Description of project ' . $cpt_proj,
                    'owner_id' => $prof->id,
                ]);

                $nbr = rand(2, $this->nbr_domains);
                $domains = Domain::all()->random($nbr);
                
                foreach ($domains as $d) {
                    $project->domains()->attach($d->id, ['importance' => rand(1, 3)]);
                }

                $tags = Tag::all()->random(rand(2, 6));
                $project->tags()->attach($tags);

                $project->save();
                $cpt_proj++;
            }
        }
    }
}
