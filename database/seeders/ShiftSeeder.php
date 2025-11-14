<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $S1 = new Shift();
        $S1->name = 'MAÑANA';
        $S1->hour_in = '06:00:00';
        $S1->hour_out = '11:59:59';
        $S1->save();

        $S2 = new Shift();
        $S2->name = 'TARDE';
        $S2->hour_in = '12:00:00';
        $S2->hour_out = '17:59:59';
        $S2->save();

        $S3 = new Shift();
        $S3->name = 'NOCTURNO';
        $S3->hour_in = '18:00:00';
        $S3->hour_out = '23:59:59';
        $S3->save();
    }
}
