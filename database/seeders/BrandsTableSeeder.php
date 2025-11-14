<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrandsTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $brands = [
            'Scania' => 'Scania especializada en  camiones pesados y autobuses.',
            'Volvo' => 'Volvo Trucks es una marca sueca de vehículos pesados.',
            'Toyota' => 'Fabricante japonés conocido por su fiabilidad y eficiencia.',
            'Ford' => 'Marca estadounidense pionera en la industria automotriz.',
            'Chevrolet' => 'Fabricante americano con una amplia gama de vehículos.',
            'Honda' => 'Empresa japonesa reconocida por sus autos y motocicletas.',
            'Nissan' => 'Marca japonesa destacada por su innovación tecnológica.',
            'BMW' => 'Marca alemana de lujo y alto rendimiento.',
            'Mercedes-Benz' => 'Reconocida marca alemana de vehículos premium.',
            'Volkswagen' => 'Fabricante alemán con modelos populares como el Golf y Passat.',
            'Hyundai' => 'Empresa surcoreana conocida por su diseño moderno y accesible.',
            'Kia' => 'Marca coreana que ofrece una buena relación calidad-precio.',
            
        ];

        foreach ($brands as $name => $description) {
            DB::table('brands')->insert([
                'name' => $name,
                'description' => $description,
                'logo' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

