<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Province;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $P1 = new Province();
        $P1->name = 'Chiclayo';
        $P1->code = '14001';
        $P1->department_id = 1;
        $P1->save();
    }
}
