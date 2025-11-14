<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            DepartmentSeeder::class,
            ProvinceSeeder::class,
            DistrictSeeder::class,
            ZoneSeeder::class,
            VehicleTypeSeeder::class,
            ShiftSeeder::class,
            ReasonsSeeder::class,
            EmployeeTypeSeeder::class,
            EmployeeSeeder::class,
            ContractSeeder::class,
            VacationSeeder::class,
            BrandsTableSeeder::class,
            ColorsTableSeeder::class,
            ModelTableSeeder::class,
            VehiclesTableSeeder::class,
            EmployeeGruopSeeder::class,
            AttendanceSeeder::class,
            SchedulingSeeder::class,
            GroupDetailSeeder::class,
        ]);

    }
}
