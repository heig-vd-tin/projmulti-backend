<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Orientation;

class OrientationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orientations = [
            ['name' => 'EAI'],
            ['name' => 'EEM'],
            ['name' => 'EN'],
            ['name' => 'EBA'],
            ['name' => 'THI'],
            ['name' => 'THO'],
            ['name' => 'IGIS'],
            ['name' => 'IGLO'],
            ['name' => 'IGQP'],
            ['name' => 'MI'],
            ['name' => 'SIC'],
        ];
        foreach($orientations as $orientation){
            Orientation::create($orientation);
        }
    }
}
