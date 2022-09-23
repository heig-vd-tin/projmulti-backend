<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [
            ['name' => '#Web'],
            ['name' => '#Programming'],
            ['name' => '#HMI'],
            ['name' => '#Physics'],
            ['name' => '#Math'],
            ['name' => '#Thermic'],
            ['name' => '#Mecanical'],
            ['name' => '#Flow'],
        ];
        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
