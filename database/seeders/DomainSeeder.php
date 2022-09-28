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
                'name' => 'Mecanic',
                'icon' => 'mdi-cog-outline',
            ],
            [
                'id' => 2,
                'name' => 'Electronic',
                'icon' => 'mdi-cpu-64-bit',
            ],
            [
                'id' => 3,
                'name' => 'Electricity',
                'icon' => 'mdi-lightning-bolt-circle',
            ],
            [
                'id' => 4,
                'name' => 'Thermic',
                'icon' => 'mdi-hydraulic-oil-temperature',
            ],
            [
                'id' => 5,
                'name' => 'Energy',
                'icon' => 'mdi-home-city-outline',
            ],
            [
                'id' => 6,
                'name' => 'Programming',
                'icon' => 'mdi-laptop',
            ],
        ];
        foreach ($datas as $data) {
            Domain::create($data);
        }
    }
}
