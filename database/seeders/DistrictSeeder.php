<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\District;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $D1 = new District();
        $D1->name = 'Jose Leonardo Ortiz';
        $D1->code = '14001';
        $D1->province_id = 1;
        $D1->department_id = 1;
        $D1->save();
    }
}
