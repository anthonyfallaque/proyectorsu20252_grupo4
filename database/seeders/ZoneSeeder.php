<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone;
use App\Models\Coord;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zone1 = new Zone();
        $zone1->name = 'Mercado Moshoqueque';
        $zone1->department_id = 1;
        $zone1->description = 'Principal centro de abastos de José Leonardo Ortiz y uno de los mercados mayoristas más grandes del norte del país.';
        $zone1->save();

        $coords1 = [
            ['latitude' => -6.7591, 'longitude' => -79.8444, 'type_coord' => 3],
            ['latitude' => -6.7580, 'longitude' => -79.8430, 'type_coord' => 3],
            ['latitude' => -6.7560, 'longitude' => -79.8438, 'type_coord' => 3],
            ['latitude' => -6.7575, 'longitude' => -79.8455, 'type_coord' => 3],
        ];

        foreach ($coords1 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone1->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }

        $zone2 = new Zone();
        $zone2->name = 'Urbanización Latina';
        $zone2->department_id = 1;
        $zone2->description = 'Zona residencial en José Leonardo Ortiz con comercios locales y servicios básicos.';
        $zone2->save();

        $coords2 = [
            ['latitude' => -6.7520, 'longitude' => -79.8390, 'type_coord' => 3],
            ['latitude' => -6.7510, 'longitude' => -79.8370, 'type_coord' => 3],
            ['latitude' => -6.7490, 'longitude' => -79.8380, 'type_coord' => 3],
            ['latitude' => -6.7500, 'longitude' => -79.8400, 'type_coord' => 3],
        ];

        foreach ($coords2 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone2->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }

        $zone3 = new Zone();
        $zone3->name = 'Urbanización San Carlos';
        $zone3->department_id = 1;
        $zone3->description = 'Área urbana de JLO con viviendas familiares y pequeños negocios.';
        $zone3->save();

        $coords3 = [
            ['latitude' => -6.7550, 'longitude' => -79.8320, 'type_coord' => 3],
            ['latitude' => -6.7540, 'longitude' => -79.8300, 'type_coord' => 3],
            ['latitude' => -6.7520, 'longitude' => -79.8310, 'type_coord' => 3],
            ['latitude' => -6.7530, 'longitude' => -79.8330, 'type_coord' => 3],
        ];

        foreach ($coords3 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone3->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }

        $zone4 = new Zone();
        $zone4->name = 'Atusparia';
        $zone4->department_id = 1;
        $zone4->description = 'Sector popular de JLO con alta densidad poblacional y comercio minorista.';
        $zone4->save();

        $coords4 = [
            ['latitude' => -6.7610, 'longitude' => -79.8380, 'type_coord' => 3],
            ['latitude' => -6.7600, 'longitude' => -79.8360, 'type_coord' => 3],
            ['latitude' => -6.7580, 'longitude' => -79.8370, 'type_coord' => 3],
            ['latitude' => -6.7590, 'longitude' => -79.8390, 'type_coord' => 3],
        ];

        foreach ($coords4 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone4->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }

        $zone5 = new Zone();
        $zone5->name = 'Urrunaga';
        $zone5->department_id = 1;
        $zone5->description = 'Zona poblada de JLO con varios servicios públicos y actividad comercial local.';
        $zone5->save();

        $coords5 = [
            ['latitude' => -6.7480, 'longitude' => -79.8450, 'type_coord' => 3],
            ['latitude' => -6.7470, 'longitude' => -79.8430, 'type_coord' => 3],
            ['latitude' => -6.7450, 'longitude' => -79.8440, 'type_coord' => 3],
            ['latitude' => -6.7460, 'longitude' => -79.8460, 'type_coord' => 3],
        ];

        foreach ($coords5 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone5->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }

        $zone6 = new Zone();
        $zone6->name = 'Nuevo San Lorenzo';
        $zone6->department_id = 1;
        $zone6->description = 'Asentamiento urbano en expansión con necesidades de infraestructura básica.';
        $zone6->save();

        $coords6 = [
            ['latitude' => -6.7430, 'longitude' => -79.8500, 'type_coord' => 3],
            ['latitude' => -6.7420, 'longitude' => -79.8480, 'type_coord' => 3],
            ['latitude' => -6.7400, 'longitude' => -79.8490, 'type_coord' => 3],
            ['latitude' => -6.7410, 'longitude' => -79.8510, 'type_coord' => 3],
        ];

        foreach ($coords6 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone6->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }

        $zone7 = new Zone();
        $zone7->name = 'Villa Hermosa';
        $zone7->department_id = 1;
        $zone7->description = 'Sector residencial de JLO con mejores condiciones de urbanización.';
        $zone7->save();

        $coords7 = [
            ['latitude' => -6.7550, 'longitude' => -79.8250, 'type_coord' => 3],
            ['latitude' => -6.7540, 'longitude' => -79.8230, 'type_coord' => 3],
            ['latitude' => -6.7520, 'longitude' => -79.8240, 'type_coord' => 3],
            ['latitude' => -6.7530, 'longitude' => -79.8260, 'type_coord' => 3],
        ];

        foreach ($coords7 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone7->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }

        $zone8 = new Zone();
        $zone8->name = 'Santa Ana';
        $zone8->department_id = 1;
        $zone8->description = 'Sector tradicional de JLO con establecimientos comerciales vecinales.';
        $zone8->save();

        $coords8 = [
            ['latitude' => -6.7630, 'longitude' => -79.8400, 'type_coord' => 3],
            ['latitude' => -6.7620, 'longitude' => -79.8380, 'type_coord' => 3],
            ['latitude' => -6.7600, 'longitude' => -79.8390, 'type_coord' => 3],
            ['latitude' => -6.7610, 'longitude' => -79.8410, 'type_coord' => 3],
        ];

        foreach ($coords8 as $index => $coord) {
            Coord::create([
                'zone_id' => $zone8->id,
                'coord_index' => $index,
                'type_coord' => $coord['type_coord'],
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
            ]);
        }
    }
}