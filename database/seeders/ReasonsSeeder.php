<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReasonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $reasons = [
            [
                'name' => 'Cambio de personal',
                'description' => 'El empleado no puede asistir por motivos de salud',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Cambio de turno',
                'description' => 'El empleado necesita cambiar su turno con otro empleado',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Cambio de vehículo',
                'description' => 'El empleado necesita cambiar su vehículo con otro empleado',
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            [
                'name' => 'Asignacion del sistema',
                'description' => 'El sistema necesita asignar un nuevo cambio',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Otro',
                'description' => 'Otros motivos no especificados',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];
        
        DB::table('reasons')->insert($reasons);
    }
}