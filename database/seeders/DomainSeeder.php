<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domain;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            [
                'id' => 1,
                'name' => 'Mécanique',
                'icon' => 'mdi-cog-outline',
            ],
            [
                'id' => 2,
                'name' => 'Électronique',
                'icon' => 'mdi-cpu-64-bit',
            ],
            [
                'id' => 3,
                'name' => 'Électricité',
                'icon' => 'mdi-lightning-bolt-circle',
            ],
            [
                'id' => 4,
                'name' => 'Thermique',
                'icon' => 'mdi-hydraulic-oil-temperature',
            ],
            [
                'id' => 5,
                'name' => 'Énergie',
                'icon' => 'mdi-home-city-outline',
            ],
            [
                'id' => 6,
                'name' => 'Programmation',
                'icon' => 'mdi-laptop',
            ],
        ];
        foreach ($datas as $data) {
            Domain::create($data);
        }
    }
}
