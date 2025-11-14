<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendance = new Attendance();
        $attendance->employee_id = 1;
        $attendance->attendance_date = now();
        $attendance->status = 1;
        $attendance->period = 0;
        $attendance->save();

        $attendance = new Attendance();
        $attendance->employee_id = 2;
        $attendance->attendance_date = now();
        $attendance->status = 1;
        $attendance->period = 0;
        $attendance->save();

        $attendance = new Attendance();
        $attendance->employee_id = 3;
        $attendance->attendance_date = now();
        $attendance->status = 1;
        $attendance->period = 0;
        $attendance->save();
        
        $attendance = new Attendance();
        $attendance->employee_id = 4;
        $attendance->attendance_date = now();
        $attendance->status = 1;
        $attendance->period = 0;
        $attendance->save();

        $attendance = new Attendance();
        $attendance->employee_id = 5;
        $attendance->attendance_date = now();
        $attendance->status = 1;
        $attendance->period = 0;
        $attendance->save();

        $attendance = new Attendance();
        $attendance->employee_id = 7;
        $attendance->attendance_date = now();
        $attendance->status = 1;
        $attendance->period = 0;
        $attendance->save();
    }
}
