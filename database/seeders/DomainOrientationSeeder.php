<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Orientations qui couvrent chaque domaine (base neuve ; en prod, créé par la migration 2600_01_01_000004). */
class DomainOrientationSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            1 => ['base' => [7, 8], 'enlarged' => []],
            2 => ['base' => [2], 'enlarged' => [7, 3]],
            3 => ['base' => [2, 3], 'enlarged' => [7]],
            4 => ['base' => [5, 6], 'enlarged' => []],
            5 => ['base' => [4], 'enlarged' => []],
            6 => ['base' => [1], 'enlarged' => [2, 7]],
        ];
        foreach ($mapping as $domain => $lists) {
            foreach ($lists['base'] as $o) {
                DB::table('domain_orientation')->insertOrIgnore(['domain_id' => $domain, 'orientation_id' => $o, 'enlarged' => false]);
            }
            foreach ($lists['enlarged'] as $o) {
                DB::table('domain_orientation')->insertOrIgnore(['domain_id' => $domain, 'orientation_id' => $o, 'enlarged' => true]);
            }
        }
    }
}
