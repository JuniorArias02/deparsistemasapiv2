<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sedes = [
            ['nombre' => 'IPS CLINICAL HOUSE'],
            ['nombre' => 'SEDE CAOBOS 2'],
            ['nombre' => 'SEDE PAMI'],
            ['nombre' => 'CORAZON SOLIDARIO'],
        ];

        DB::table('sedes')->insert($sedes);
    }
}
