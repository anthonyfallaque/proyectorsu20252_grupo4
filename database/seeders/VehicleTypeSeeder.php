<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicletype;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $V1 = new Vehicletype();
        $V1->name = 'COMPACTADORA';
        $V1->save();

        $V2 = new Vehicletype();
        $V2->name = 'VOLQUETE';
        $V2->save();
    }
}
