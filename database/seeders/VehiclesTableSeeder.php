<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehiclesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('vehicles')->truncate();

        $vehicles = [];

        for ($i = 1; $i <= 10; $i++) {
            $vehicles[] = [
                'name' => 'Vehículo ' . $i,
                'code' => 'VEH-' . strtoupper(Str::random(5)),
                'plate' => strtoupper(Str::random(3)) . '-' . rand(100, 999),
                'year' => rand(2010, 2024),
                'load_capacity' => rand(2000, 12000), 
                'fuel_capacity' => rand(40, 150), 
                'compactation_capacity' => rand(0, 500), 
                'people_capacity' => rand(1, 3),
                'description' => 'Vehículo de tipo industrial modelo ' . $i,
                'status' => rand(0, 1), 

          
                'color_id' => rand(1, 3),       
                'brand_id' => rand(1, 10),    
                'type_id' => rand(1, 2),        
                'model_id' => rand(1, 10),      

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('vehicles')->insert($vehicles);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
