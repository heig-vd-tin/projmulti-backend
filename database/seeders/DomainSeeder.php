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
                'name' => 'Mecanic'
            ],
            [
                'name' => 'Electronic'
            ],
            [
                'name' => 'Electricity'
            ],
            [
                'name' => 'Thermic'
            ],
            [
                'name' => 'Energy'
            ],
            [
                'name' => 'Programming'
            ],
        ];
        foreach ($datas as $data) {
            Domain::create($data);
        }
    }
}
