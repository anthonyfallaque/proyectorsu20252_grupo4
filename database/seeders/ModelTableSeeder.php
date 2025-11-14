<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ModelTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('brandmodels')->insert([
            [
                'name' => 'Scania Touring',
                'code' => 'SCT',
                'description' => 'Sedán compacto',
                'brand_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Scania Metrolink',
                'code' => 'SCM',
                'description' => 'popular en Asia',
                'brand_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Scania Citywide ',
                'code' => 'SCC',
                'description' => 'autobús urbano',
                'brand_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Volvo FH',
                'code' => 'VFH',
                'description' => 'Transporte internacional ',
                'brand_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Volvo 7900 Electric',
                'code' => 'V79',
                'description' => 'Autobús eléctrico urbano',
                'brand_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Volvo B11R/B8R',
                'code' => 'VB1',
                'description' => 'Autobús para transporte',
                'brand_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Silverado',
                'code' => 'SIL',
                'description' => 'Pickup grande',
                'brand_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Malibu',
                'code' => 'MAL',
                'description' => 'Sedán mediano',
                'brand_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Impala',
                'code' => 'IMP',
                'description' => 'Sedán grande',
                'brand_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Civic',
                'code' => 'CIV',
                'description' => 'Sedán compacto',
                'brand_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
