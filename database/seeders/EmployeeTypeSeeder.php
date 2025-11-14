<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmployeeType;

class EmployeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $employeeTypes = [
            [
                'name' => 'Conductor',
                'description' => 'Encargado de conducir el vehículo de recolección de basura'
            ],
            [
                'name' => 'Ayudante',
                'description' => 'Asistente en la recolección y manipulación de residuos'
            ],
            [
                'name' => 'Supervisor',
                'description' => 'Supervisa las rutas y equipos de recolección'
            ],
            [
                'name' => 'Administrativo',
                'description' => 'Personal de oficina y administración'
            ]
        ];

        foreach ($employeeTypes as $type) {
            EmployeeType::create($type);
        }
    }
}