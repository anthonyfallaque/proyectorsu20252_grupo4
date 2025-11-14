<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Vacation;
use Carbon\Carbon;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $contracts = [
            [
                'employee_id' => 1,
                'contract_type' => 'Nombrado',
                'start_date' => $now->subMonths(6)->format('Y-m-d'),
                'end_date' => null,
                'salary' => 3500.00,
                'position_id' => 1,
                'department_id' => 1,
                'vacation_days_per_year' => 20, 
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 2,
                'contract_type' => 'Temporal',
                'start_date' => '2025-06-01',
                'end_date' => '2025-09-15', 
                'salary' => 1500.00,
                'position_id' => 2,
                'department_id' => 1,
                'vacation_days_per_year' => 0,
                'is_active' => true, 
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 3,
                'contract_type' => 'Contrato permanente',
                'start_date' => $now->subMonths(12)->format('Y-m-d'),
                'end_date' => null,
                'salary' => 4000.00,
                'position_id' => 3,
                'department_id' => 1,
                'vacation_days_per_year' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 4,
                'contract_type' => 'Temporal',
                'start_date' => '2024-09-01',
                'end_date' => '2025-04-13', 
                'salary' => 1800.00,
                'position_id' => 4,
                'department_id' => 1,
                'vacation_days_per_year' => 0,
                'is_active' => false, 
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 5,
                'contract_type' => 'Contrato permanente',
                'start_date' => $now->subMonths(8)->format('Y-m-d'),
                'end_date' => null,
                'salary' => 3800.00,
                'position_id' => 1,
                'department_id' => 1,
                'vacation_days_per_year' => 18,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 6,
                'contract_type' => 'Nombrado',
                'start_date' => $now->subMonths(18)->format('Y-m-d'),
                'end_date' => null,
                'salary' => 4200.00,
                'position_id' => 2,
                'department_id' => 1,
                'vacation_days_per_year' => 30, 
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 7,
                'contract_type' => 'Nombrado',
                'start_date' => $now->subMonths(24)->format('Y-m-d'),
                'end_date' => null,
                'salary' => 4500.00,
                'position_id' => 3,
                'department_id' => 1,
                'vacation_days_per_year' => 30, 
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => 8,
                'contract_type' => 'Temporal',
                'start_date' => $now->subMonths(1)->format('Y-m-d'),
                'end_date' => $now->addMonths(5)->format('Y-m-d'),
                'salary' => 1600.00,
                'position_id' => 2,
                'department_id' => 1,
                'vacation_days_per_year' => 0,
                'is_active' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        
        DB::table('contracts')->insert($contracts);
        
    }
    
}