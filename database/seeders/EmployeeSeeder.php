<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeTypes = EmployeeType::all();

        if ($employeeTypes->isEmpty()) {
            $this->command->info('No hay tipos de empleados registrados. No se pueden crear empleados.');
            return;
        }

        $conductor = $employeeTypes->where('name', 'Conductor')->first()?->id ?? $employeeTypes->first()->id;
        $ayudante = $employeeTypes->where('name', 'Ayudante')->first()?->id ?? $employeeTypes->first()->id;
        $supervisor = $employeeTypes->where('name', 'Supervisor')->first()?->id ?? $employeeTypes->first()->id;
        $administrativo = $employeeTypes->where('name', 'Administrativo')->first()?->id ?? $employeeTypes->first()->id;

        $E1 = new Employee();
        $E1->dni = '74561239';
        $E1->lastnames = 'Salazar Muñoz';
        $E1->names = 'Carlos Eduardo';
        $E1->birthday = Carbon::createFromDate(1986, 4, 10);
        $E1->license = 'L74561239';
        $E1->address = 'Av. Grau 150, Chiclayo';
        $E1->email = 'carlos.salazar@empresa.com';
        $E1->photo = 'no_image.png';
        $E1->phone = '987320145';
        $E1->status = true;
        $E1->password = Hash::make('password123');
        $E1->type_id = $conductor;
        $E1->save();

        $E2 = new Employee();
        $E2->dni = '71829364';
        $E2->lastnames = 'Paredes Rojas';
        $E2->names = 'Lucía Fernanda';
        $E2->birthday = Carbon::createFromDate(1994, 9, 25);
        $E2->license = 'L71829364';
        $E2->address = 'Jr. Ica 245, Trujillo';
        $E2->email = 'lucia.paredes@empresa.com';
        $E2->photo = 'no_image.png';
        $E2->phone = '912468357';
        $E2->status = true;
        $E2->password = Hash::make('password123');
        $E2->type_id = $ayudante;
        $E2->save();

        $E3 = new Employee();
        $E3->dni = '68923471';
        $E3->lastnames = 'Ramírez Soto';
        $E3->names = 'Andrés Felipe';
        $E3->birthday = Carbon::createFromDate(1989, 7, 14);
        $E3->license = 'L68923471';
        $E3->address = 'Calle Los Álamos 520, Lima';
        $E3->email = 'andres.ramirez@empresa.com';
        $E3->photo = 'no_image.png';
        $E3->phone = '934156782';
        $E3->status = true;
        $E3->password = Hash::make('password123');
        $E3->type_id = $ayudante;
        $E3->save();

        $E4 = new Employee();
        $E4->dni = '73562819';
        $E4->lastnames = 'Huamán Torres';
        $E4->names = 'Paola Milagros';
        $E4->birthday = Carbon::createFromDate(1993, 1, 9);
        $E4->license = 'L73562819';
        $E4->address = 'Av. Los Héroes 350, Piura';
        $E4->email = 'paola.huaman@empresa.com';
        $E4->photo = 'no_image.png';
        $E4->phone = '965821347';
        $E4->status = true;
        $E4->password = Hash::make('password123');
        $E4->type_id = $conductor;
        $E4->save();

        $E5 = new Employee();
        $E5->dni = '75683924';
        $E5->lastnames = 'Vega Ruiz';
        $E5->names = 'José Martín';
        $E5->birthday = Carbon::createFromDate(1981, 10, 20);
        $E5->license = 'L75683924';
        $E5->address = 'Calle Junín 120, Cusco';
        $E5->email = 'jose.vega@empresa.com';
        $E5->photo = 'no_image.png';
        $E5->phone = '923567489';
        $E5->status = true;
        $E5->password = Hash::make('password123');
        $E5->type_id = $ayudante;
        $E5->save();

        $E6 = new Employee();
        $E6->dni = '70294518';
        $E6->lastnames = 'Cáceres León';
        $E6->names = 'Mónica Isabel';
        $E6->birthday = Carbon::createFromDate(1997, 12, 2);
        $E6->license = 'L70294518';
        $E6->address = 'Av. Arequipa 1200, Lima';
        $E6->email = 'monica.caceres@empresa.com';
        $E6->photo = 'no_image.png';
        $E6->phone = '978456312';
        $E6->status = false;
        $E6->password = Hash::make('password123');
        $E6->type_id = $ayudante;
        $E6->save();

        $E7 = new Employee();
        $E7->dni = '71928345';
        $E7->lastnames = 'Núñez Campos';
        $E7->names = 'Ricardo Daniel';
        $E7->birthday = Carbon::createFromDate(1988, 6, 11);
        $E7->license = 'L71928345';
        $E7->address = 'Calle Los Cedros 310, Arequipa';
        $E7->email = 'ricardo.nunez@empresa.com';
        $E7->photo = 'no_image.png';
        $E7->phone = '912345978';
        $E7->status = true;
        $E7->password = Hash::make('password123');
        $E7->type_id = $supervisor;
        $E7->save();

        $E8 = new Employee();
        $E8->dni = '74819362';
        $E8->lastnames = 'López Espinoza';
        $E8->names = 'Tatiana Sofía';
        $E8->birthday = Carbon::createFromDate(1995, 5, 23);
        $E8->license = 'L74819362';
        $E8->address = 'Jr. Huallaga 900, Lima';
        $E8->email = 'tatiana.lopez@empresa.com';
        $E8->photo = 'no_image.png';
        $E8->phone = '989176543';
        $E8->status = true;
        $E8->password = Hash::make('password123');
        $E8->type_id = $ayudante;
        $E8->save();

        $E9 = new Employee();
        $E9->dni = '71324958';
        $E9->lastnames = 'Pérez Aguilar';
        $E9->names = 'Sergio Alonso';
        $E9->birthday = Carbon::createFromDate(1984, 8, 5);
        $E9->license = 'L71324958';
        $E9->address = 'Av. Primavera 520, Lima';
        $E9->email = 'sergio.perez@empresa.com';
        $E9->photo = 'no_image.png';
        $E9->phone = '991347852';
        $E9->status = true;
        $E9->password = Hash::make('password123');
        $E9->type_id = $ayudante;
        $E9->save();

        $E10 = new Employee();
        $E10->dni = '72469583';
        $E10->lastnames = 'Zapata Villanueva';
        $E10->names = 'Camila Adriana';
        $E10->birthday = Carbon::createFromDate(1992, 11, 28);
        $E10->license = 'L72469583';
        $E10->address = 'Av. Progreso 430, Chiclayo';
        $E10->email = 'camila.zapata@empresa.com';
        $E10->photo = 'no_image.png';
        $E10->phone = '998345712';
        $E10->status = true;
        $E10->password = Hash::make('password123');
        $E10->type_id = $administrativo;
        $E10->save();
    }
}
