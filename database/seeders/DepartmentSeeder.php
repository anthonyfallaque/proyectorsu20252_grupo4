<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $D1 = new Department();
        $D1->name = 'Lambayeque';
        $D1->code = 'LAM';
        $D1->save();

    }
}
