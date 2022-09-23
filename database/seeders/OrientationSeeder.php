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
            [
                'acronym' => 'EAI', 'faculty_acronym' => 'ELCI', 'department_acronym' => 'TIN',
                'name' => 'Electronique - Automatisation industrielle', 'faculty_name' => 'Génie électrique'
            ],
            [
                'acronym' => 'EEM', 'faculty_acronym' => 'ELCI', 'department_acronym' => 'TIN',
                'name' => 'Electronique embarquée - Mécatronique', 'faculty_name' => 'Génie électrique'
            ],
            [
                'acronym' => 'EN', 'faculty_acronym' => 'ELCI', 'department_acronym' => 'TIN',
                'name' => 'Systèmes énergétiques', 'faculty_name' => 'Génie électrique'
            ],

            [
                'acronym' => 'EBA', 'faculty_acronym' => 'ETE', 'department_acronym' => 'TIN',
                'name' => 'Energétique du bâtiment', 'faculty_name' => 'Energie et techniques environnementales'
            ],
            [
                'acronym' => 'THI', 'faculty_acronym' => 'ETE', 'department_acronym' => 'TIN',
                'name' => 'Thermique industrielle', 'faculty_name' => 'Energie et techniques environnementales'
            ],
            [
                'acronym' => 'THO', 'faculty_acronym' => 'ETE', 'department_acronym' => 'TIN',
                'name' => 'Thermotronique', 'faculty_name' => 'Energie et techniques environnementales'
            ],

            /*[
                'acronym' => 'IGIS', 'faculty_acronym' => 'IGIS', 'department_acronym' => 'TIN',
                'name' => 'Ingénierie et gestion industrielles - Tronc commun', 'faculty_name' => 'Ingénierie et gestion industrielles'
            ],
            [
                'acronym' => 'IGLO', 'faculty_acronym' => 'IGIS', 'department_acronym' => 'TIN',
                'name' => 'Logistique et organisation industrielles', 'faculty_name' => 'Ingénierie et gestion industrielles'
            ],
            [
                'acronym' => 'IGQP', 'faculty_acronym' => 'IGIS', 'department_acronym' => 'TIN',
                'name' => 'Qualité et performances industrielles', 'faculty_name' => 'Ingénierie et gestion industrielles'
            ],*/

            [
                'acronym' => 'MI', 'faculty_acronym' => 'MTEC', 'department_acronym' => 'TIN',
                'name' => 'Microtechniques', 'faculty_name' => 'Microtechniques'
            ],

            [
                'acronym' => 'SIC', 'faculty_acronym' => 'SYND', 'department_acronym' => 'TIN',
                'name' => 'Conception', 'faculty_name' => 'Systèmes industriels'
            ],
        ];
        foreach ($orientations as $orientation) {
            Orientation::create($orientation);
        }
    }
}
