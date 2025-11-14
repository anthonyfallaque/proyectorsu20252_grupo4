<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ColorsTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('colors')->insert([
            [
                'name' => 'Rojo',
                'description' => '#FF0000',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Azul',
                'description' => '#0000FF',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Verde',
                'description' => '#00FF00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
