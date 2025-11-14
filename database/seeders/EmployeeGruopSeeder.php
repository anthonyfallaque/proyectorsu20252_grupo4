<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employeegroup;

class EmployeeGruopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeGroup = new Employeegroup();
        $employeeGroup->name = 'MERCMOSHMÑN01';
        $employeeGroup->zone_id = 1;
        $employeeGroup->shift_id = 1;
        $employeeGroup->vehicle_id = 1;
        $employeeGroup->days = 'Lunes,Miércoles,Jueves';
        $employeeGroup->status = 1;
        $employeeGroup->save();

        $employeeGroup = new Employeegroup();
        $employeeGroup->name = 'URBLATMÑN01';
        $employeeGroup->zone_id = 2;
        $employeeGroup->shift_id = 1;
        $employeeGroup->vehicle_id = 2;
        $employeeGroup->days = 'Martes,Jueves,Sábado';
        $employeeGroup->status = 1;
        $employeeGroup->save();
    }
}
