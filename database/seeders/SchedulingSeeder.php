<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Scheduling;

class SchedulingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scheduling = new Scheduling();
        $scheduling->group_id = 1;
        $scheduling->shift_id = 1;
        $scheduling->vehicle_id = 1;
        $scheduling->zone_id = 1;
        $scheduling->date = now();
        $scheduling->status = 1;
        $scheduling->save();

        $scheduling = new Scheduling();
        $scheduling->group_id = 2;
        $scheduling->shift_id = 1;
        $scheduling->vehicle_id = 2;
        $scheduling->zone_id = 2;
        $scheduling->date = now();
        $scheduling->status = 1;
        $scheduling->save();
    }
}
