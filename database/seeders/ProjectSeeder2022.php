<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\Domain;
use App\Models\Tag;
use Faker\Generator;
use Illuminate\Container\Container;

class ProjectSeeder2022 extends Seeder
{
    public function run()
    {
        $faker = Container::getInstance()->make(Generator::class);

        $owner_name = "Baillifard";
        $title = "Micro‐malterie";
        $ref = "BAM1";
        $doms = [1,6,3,4,5];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Baillifard";
        $title = "Fermenteur";
        $ref = "BAM2";
        $doms = [1,6,3,4,5];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Hochet";
        $title = "Graveuse";
        $ref = "BHT1";
        $doms = [1,6,3,2,4,5];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Hochet";
        $title = "Cidrerie";
        $ref = "BHT2";
        $doms = [1,2,3,4,5,6];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Mentano";
        $title = "MatitONE";
        $ref = "CMO1";
        $doms = [6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Mentano";
        $title = "Palonnier";
        $ref = "CMO2";
        $doms = [1,6,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Dumas";
        $title = "cylindre";
        $ref = "DJF1";
        $doms = [1];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Dumas";
        $title = "Oxymètre";
        $ref = "DJF2";
        $doms = [6,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Boillat";
        $title = "polaire";
        $ref = "EBA1";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Boillat";
        $title = "FDM‐4 axes";
        $ref = "EBA2";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Birling";
        $title = "Arpegio";
        $ref = "FBG1";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Birling";
        $title = "Kinetic";
        $ref = "FBG2";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Krummen";
        $title = "Rocket";
        $ref = "KRM1";
        $doms = [1,6,3,2,4,5];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Krummen";
        $title = "absenic";
        $ref = "KRM2";
        $doms = [1,6,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Gravier";
        $title = "HydroBike";
        $ref = "LGN1";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Gravier";
        $title = "Pendule";
        $ref = "LGN2";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Demierre";
        $title = "Biofeedback";
        $ref = "MD1";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Bozorg";
        $title = "Bobine";
        $ref = "MRB1";
        $doms = [6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Bozorg";
        $title = "Condensateur";
        $ref = "MRB2";
        $doms = [6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Ropp";
        $title = "ExtruPlast";
        $ref = "ROJ1";
        $doms = [1,6,3,2,4,5];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Ropp";
        $title = "MillPlast";
        $ref = "ROJ2";
        $doms = [1,6,3,2,4,5];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Maulaz";
        $title = "Trieuse";
        $ref = "TMZ1";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Maulaz";
        $title = "Distributeur";
        $ref = "TMZ2";
        $doms = [1,6,3,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Chevallier";
        $title = "Sunae";
        $ref = "YCR1";
        $doms = [1,6,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);

        $owner_name = "Chevallier";
        $title = "WireBender";
        $ref = "YCR2";
        $doms = [1,6,2];
        $this->insert($faker, $owner_name, $title, $ref, $doms);
    }

    function insert($faker, $owner_name, $title, $ref, $doms){
        
        $owner = User::where('lastname', 'like', $owner_name)->first();

        if( !isset($owner) )
            dd("User $owner_name not found");

        $project = Project::create([
            'title' => $title,
            'reference' => $ref,
            'short_description' => $faker->realText( rand(20, 100) ),
            'description' => $faker->realText( rand(20, 800) ), //'Description of project ' . $cpt_proj,
            'owner_id' => $owner->id,
        ]);

        $domains = Domain::whereIn('id', $doms)->get();
        
        foreach ($domains as $d) {
            $project->domains()->attach($d->id, ['importance' => 2]);
        }

        $tags = Tag::all()->random(rand(1, 3));
        $project->tags()->attach($tags);

        $project->save();
    }
}
