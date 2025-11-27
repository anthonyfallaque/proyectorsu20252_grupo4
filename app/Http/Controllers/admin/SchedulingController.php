<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scheduling;
use App\Models\Shift;
use App\Models\EmployeeGroup;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Employee;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Models\EmployeeType;
use Carbon\Carbon;
use App\Models\Groupdetail;
use App\Models\Reason;
use Illuminate\Support\Facades\DB;
use App\Models\Change;
use App\Models\Attendance;
use App\Models\Vacation;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Result\Reason\Reason as ReasonReason;

class SchedulingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $fechaActual = Carbon::now()->format('Y-m-d');

        if ($request->ajax()) {
            // Inicia la consulta
            $query = Scheduling::with('employeegroup', 'shift', 'vehicle');

            // Aplica los filtros de fecha si están presentes

            if($request->filled('start_date') && !$request->filled('end_date')){
                    $query->whereDate('date', '=', $request->start_date);
            }else{
                if ($request->filled('start_date')) {
                    $query->whereDate('date', '>=', $request->start_date);
                }

                if ($request->filled('end_date')) {
                    $query->whereDate('date', '<=', $request->end_date);
                }
            }


            // Ejecuta la consulta y obtiene los resultados
            $schedulings = $query->get();

            return DataTables::of($schedulings)
                ->addColumn('date', function ($scheduling) {
                    return $scheduling->date;
                })
                ->addColumn('status_badge', function ($scheduling) {
                    if ($scheduling->status == 1) {
                        return '<span class="badge badge-secondary">Programado</span>';
                    } elseif ($scheduling->status == 2) {
                        return '<span class="badge badge-success">Completado</span>';
                    } elseif ($scheduling->status == 0) {
                        return '<span class="badge badge-danger">Cancelado</span>';
                    } else {
                        return '<span class="badge badge-warning">Reprogramado</span>';
                    }
                })
                ->addColumn('shift', function ($scheduling) {
                    return $scheduling->shift->name;
                })
                ->addColumn('vehicle', function ($scheduling) {
                    return $scheduling->vehicle->code;
                })
                ->addColumn('zone', function ($scheduling) {
                    return $scheduling->employeegroup->zone->name;
                })
                ->addColumn('group', function ($scheduling) {
                    return $scheduling->employeegroup->name;
                })
                ->addColumn('action', function ($scheduling) {
                    $editBtn = '<button class="btn btn-warning btn-sm btnEditar" alt="Reprogramar" id="' . $scheduling->id . '">
                                    <i class="fas fa-retweet"></i>
                                </button>';

                    $viewBtn = '<button class="btn btn-info btn-sm btnVer" alt="Ver" id="' . $scheduling->id . '">
                                <i class="fas fa-users"></i>
                            </button>';

                    $deleteBtn = '<form class="delete d-inline" action="' . route('admin.schedulings.destroy', $scheduling->id) . '" method="POST">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm" alt="Cancelar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>';

                    return $editBtn . ' ' . $viewBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('admin.schedulings.index', compact('fechaActual'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shifts = Shift::all();

        return view('admin.schedulings.create', compact('shifts'));
    }

    public function ValidationContenido(Request $request)
    {
        $ListaNoRegistros = [];
        $start_date = $request->start_date;

        if ($request->end_date) {
            $end_date = $request->end_date;
        } else {
            $end_date = $request->start_date;
        }

        foreach ($request->groups as $group) {
            // Validar conductor
            $vacation = $this->checkVacation($group['driver_id'], $start_date, $end_date);
            if ($vacation) {
                if (!in_array($group['employee_group_id'], $ListaNoRegistros)) {
                    array_push($ListaNoRegistros, $group['employee_group_id']);
                }
            }

            // Validar ayudantes
            $helpers = $group['helpers'] ?? [];
            foreach ($helpers as $helper) {
                $vacation = $this->checkVacation($helper, $start_date, $end_date);
                if ($vacation) {
                    if (!in_array($group['employee_group_id'], $ListaNoRegistros)) {
                        array_push($ListaNoRegistros, $group['employee_group_id']);
                    }
                }
            }
        }

        return $ListaNoRegistros;
    }

    private function checkVacation($employeeId, $start_date, $end_date)
    {
        return Vacation::with('employee')
            ->where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->whereDate('request_date', '<=', $end_date)
            ->whereDate('end_date', '>=', $start_date)
            ->first();
    }


    public function store(Request $request)
{
    try {
        if (!$request->start_date) {
            return response()->json([
                'message' => 'La fecha de inicio es requerida'
            ], 400);
        }

        $end_date = $request->end_date ?? $request->start_date;
        $todosLosErrores = []; // Array para acumular TODOS los errores de validación

        // FASE 1: VALIDAR TODO ANTES DE GUARDAR NADA
        foreach ($request->groups as $group) {
            $employeeGroup = EmployeeGroup::find($group['employee_group_id']);

            if (!$employeeGroup) {
                $todosLosErrores[] = "El grupo {$group['employee_group_id']} no existe";
                continue;
            }

            $groupDays = explode(',', $employeeGroup->days);
            $daysOfWeek = [
                'Lunes' => Carbon::MONDAY,
                'Martes' => Carbon::TUESDAY,
                'Miércoles' => Carbon::WEDNESDAY,
                'Jueves' => Carbon::THURSDAY,
                'Viernes' => Carbon::FRIDAY,
                'Sábado' => Carbon::SATURDAY,
                'Domingo' => Carbon::SUNDAY,
            ];

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($end_date);
            $helpers = $group['helpers'] ?? [];

            // Validar cada día que se va a programar
            while ($startDate->lte($endDate)) {
                if (in_array($startDate->dayOfWeek, array_map(function($day) use ($daysOfWeek) {
                    return $daysOfWeek[$day];
                }, $groupDays))) {

                    $dateStr = $startDate->toDateString();

                    // Ejecutar TODAS las validaciones (incluyendo vacaciones)
                    $validations = $this->runAllValidationsWithVacations(
                        $group['driver_id'],
                        $helpers,
                        $employeeGroup->vehicle_id,
                        $employeeGroup->shift_id,
                        $employeeGroup->zone_id,
                        $dateStr,
                        $employeeGroup->name,
                        $end_date
                    );

                    if (!$validations['valid']) {
                        $todosLosErrores = array_merge($todosLosErrores, $validations['errors']);
                    }
                }

                $startDate->addDay();
            }

            // Resetear fecha para el siguiente grupo
            $startDate = Carbon::parse($request->start_date);
        }

        // FASE 2: SI HAY ERRORES, AGRUPAR Y MOSTRAR RESUMEN
        if (!empty($todosLosErrores)) {
            // Agrupar errores por tipo y empleado
            $erroresAgrupados = $this->agruparErrores($todosLosErrores);

            return response()->json([
                'message' => 'No se puede crear la programación debido a los siguientes errores:',
                'errors' => $erroresAgrupados
            ], 400);
        }

        // FASE 3: SI TODO ESTÁ VÁLIDO, PROCEDER A GUARDAR
        DB::beginTransaction();

        foreach ($request->groups as $group) {
            $employeeGroup = EmployeeGroup::find($group['employee_group_id']);
            $groupDays = explode(',', $employeeGroup->days);

            $daysOfWeek = [
                'Lunes' => Carbon::MONDAY,
                'Martes' => Carbon::TUESDAY,
                'Miércoles' => Carbon::WEDNESDAY,
                'Jueves' => Carbon::THURSDAY,
                'Viernes' => Carbon::FRIDAY,
                'Sábado' => Carbon::SATURDAY,
                'Domingo' => Carbon::SUNDAY,
            ];

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($end_date);
            $helpers = $group['helpers'] ?? [];

            while ($startDate->lte($endDate)) {
                if (in_array($startDate->dayOfWeek, array_map(function($day) use ($daysOfWeek) {
                    return $daysOfWeek[$day];
                }, $groupDays))) {

                    $dateStr = $startDate->toDateString();

                    // Crear scheduling (ya validado)
                    $scheduling = Scheduling::create([
                        'date' => $dateStr,
                        'group_id' => $group['employee_group_id'],
                        'shift_id' => $employeeGroup->shift_id,
                        'vehicle_id' => $employeeGroup->vehicle_id,
                        'zone_id' => $employeeGroup->zone_id,
                        'notes' => '',
                        'status' => 1,
                    ]);

                    // Crear conductor
                    Groupdetail::create([
                        'employee_id' => $group['driver_id'],
                        'scheduling_id' => $scheduling->id,
                    ]);

                    // Crear ayudantes
                    foreach ($helpers as $helper) {
                        Groupdetail::create([
                            'employee_id' => $helper,
                            'scheduling_id' => $scheduling->id,
                        ]);
                    }
                }

                $startDate->addDay();
            }
        }

        DB::commit();

        return response()->json([
            'success' => 'Todas las programaciones se crearon correctamente.'
        ], 200);

    } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error al crear la programación: ' . $th->getMessage()
        ], 500);
    }
}





/**
 * Agrupar errores similares para mostrar un resumen conciso
 */
private function agruparErrores($errores)
{
    $agrupados = [];
    $vacaciones = [];
    $contratos = [];
    $disponibilidad = [];
    $vehiculos = [];
    $duplicados = [];

    foreach ($errores as $error) {
        // Vacaciones
        if (strpos($error, 'tiene vacaciones') !== false) {
            preg_match('/\] (El (?:conductor|ayudante) .+?) tiene vacaciones del (.+?) al (.+)/', $error, $matches);
            if ($matches) {
                $key = $matches[1]; // "El conductor Nombre Apellido"
                if (!isset($vacaciones[$key])) {
                    $vacaciones[$key] = [
                        'mensaje' => $matches[1],
                        'fecha_inicio' => $matches[2],
                        'fecha_fin' => $matches[3]
                    ];
                }
            }
        }
        // Contratos inactivos
        elseif (strpos($error, 'no tiene contrato activo') !== false) {
            preg_match('/\] (El empleado .+?) no tiene contrato/', $error, $matches);
            if ($matches) {
                $key = $matches[1];
                if (!isset($contratos[$key])) {
                    $contratos[$key] = $matches[1] . ' no tiene contrato activo para las fechas seleccionadas';
                }
            }
        }
        // Ya está programado
        elseif (strpos($error, 'ya está programado') !== false) {
            preg_match('/\] (El empleado .+?) ya está programado/', $error, $matches);
            if ($matches) {
                $key = $matches[1];
                if (!isset($disponibilidad[$key])) {
                    $disponibilidad[$key] = $matches[1] . ' ya está programado en otro grupo para las fechas seleccionadas';
                }
            }
        }
        // Vehículo
        elseif (strpos($error, 'vehículo') !== false && strpos($error, 'mantenimiento') !== false) {
            preg_match('/\] (El vehículo .+?) tiene mantenimiento/', $error, $matches);
            if ($matches) {
                $key = $matches[1];
                if (!isset($vehiculos[$key])) {
                    $vehiculos[$key] = $matches[1] . ' tiene mantenimiento programado en las fechas seleccionadas';
                }
            }
        }
        elseif (strpos($error, 'vehículo') !== false && strpos($error, 'ya está programado') !== false) {
            preg_match('/\] (El vehículo .+?) ya está programado/', $error, $matches);
            if ($matches) {
                $key = $matches[1];
                if (!isset($vehiculos[$key])) {
                    $vehiculos[$key] = $matches[1] . ' ya está programado para el turno seleccionado en las fechas indicadas';
                }
            }
        }
        // Duplicados
        elseif (strpos($error, 'programación idéntica') !== false) {
            if (!in_array('Ya existe una programación idéntica para estas fechas, turno, vehículo y personal', $duplicados)) {
                $duplicados[] = 'Ya existe una programación idéntica para estas fechas, turno, vehículo y personal';
            }
        }
    }

    // Construir array final de errores agrupados
    foreach ($vacaciones as $v) {
        $agrupados[] = "{$v['mensaje']} tiene vacaciones del {$v['fecha_inicio']} al {$v['fecha_fin']}";
    }

    foreach ($contratos as $c) {
        $agrupados[] = $c;
    }

    foreach ($disponibilidad as $d) {
        $agrupados[] = $d;
    }

    foreach ($vehiculos as $vh) {
        $agrupados[] = $vh;
    }

    foreach ($duplicados as $dup) {
        $agrupados[] = $dup;
    }

    return $agrupados;
}



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Obtener la programación con sus detalles de grupo, empleados y tipos
        $scheduling = Scheduling::with([
            'groupdetail',
            'groupdetail.employee',
            'groupdetail.employee.employeeType',
            'employeegroup',
            'employeegroup.shift',
            'employeegroup.vehicle',
            'employeegroup.zone',
        ])->findOrFail($id);

        $changes = DB::select("
            SELECT
        c.id,
        c.scheduling_id,
        c.change_date,
        c.notes,
        c.created_at,
        e_old.names AS old_employee_name,
        e_new.names AS new_employee_name,
        v_old.plate AS old_vehicle_plate,
        v_new.plate AS new_vehicle_plate,
        s_old.name AS old_shift_name,
        s_new.name AS new_shift_name,
        r.name AS reason_name
        FROM changes c
        LEFT JOIN employees e_old ON c.old_employee_id = e_old.id
        LEFT JOIN employees e_new ON c.new_employee_id = e_new.id
        LEFT JOIN vehicles v_old ON c.old_vehicle_id = v_old.id
        LEFT JOIN vehicles v_new ON c.new_vehicle_id = v_new.id
        LEFT JOIN shifts s_old ON c.old_shift_id = s_old.id
        LEFT JOIN shifts s_new ON c.new_shift_id = s_new.id
        LEFT JOIN reasons r ON c.reason_id = r.id
        WHERE c.scheduling_id = ?
        ORDER BY c.change_date DESC
            ", [$id]);



        // Pasar los datos a la vista
        return view('admin.schedulings.show', compact('scheduling', 'changes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $scheduling = Scheduling::findOrFail($id);
        $employeeGroup = EmployeeGroup::where('id', $scheduling->group_id)->first();
        $reasons = Reason::all();
        $shifts = Shift::all();
        $vehicles = Vehicle::all();
        $personal = Groupdetail::where('scheduling_id', $id)
        ->with('employee','employee.employeeType')
        ->get();
        $personalDisponible = Employee::whereHas('contracts', function ($query) {
                $query->where('is_active', 1);
            })
            ->get();

        return view('admin.schedulings.edit', compact('scheduling', 'reasons', 'shifts', 'vehicles', 'personal', 'employeeGroup', 'personalDisponible'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $scheduling = Scheduling::findOrFail($id);
            $scheduling->update([
                'status' => 0
            ]);
            return response()->json([
                'message' => 'Programación eliminada correctamente.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar la programación.' . $th->getMessage()
            ], 500);
        }
    }


    public function editModule(string $id)
    {
        $scheduling = Scheduling::findOrFail($id);
        $employeeGroup = EmployeeGroup::where('id', $scheduling->group_id)->first();
        $reasons = Reason::all();
        $shifts = Shift::all();
        $vehicles = Vehicle::all();

        $personal = Groupdetail::where('scheduling_id', $id)
        ->with('employee','employee.employeeType')
        ->get();

        $personalIds = $personal->pluck('employee_id');

        $personalAsistido = Attendance::with('employee', 'employee.employeeType')
        ->whereIn('employee_id', $personalIds)
        ->whereDate('attendance_date', $scheduling->date)
        ->get();

        // Obtener IDs de los que asistieron
        $asistieronIds = $personalAsistido->pluck('employee_id');

        // Obtener al personal que NO asistió (filtrando los que no están en la lista de asistencia)
        $personalNoAsistido = $personal->filter(function ($item) use ($asistieronIds) {
            return !$asistieronIds->contains($item->employee_id);
        });


        $fecha = $scheduling->date;
        $turnoId = optional($scheduling->employeegroup)->shift_id;

        // 2. Obtener TODAS las schedulings de ese mismo turno y fecha
        $schedulingsMismoTurno = Scheduling::whereDate('date', $fecha)
            ->whereHas('employeegroup', function ($q) use ($turnoId) {
                $q->where('shift_id', $turnoId);
            })->pluck('id');

        // 3. Obtener los employee_id ya asignados en cualquier programación de esa fecha y turno
        $empleadosAsignados = Groupdetail::whereIn('scheduling_id', $schedulingsMismoTurno)
            ->pluck('employee_id');

        // 4. Obtener los employee_id que asistieron ese día
        $empleadosConAsistencia = Attendance::whereDate('attendance_date', $fecha)
            ->pluck('employee_id');

        // 5. Empleados que asistieron pero no están en ningún Groupdetail de ese turno y fecha
        $personalDisponible = Employee::whereIn('id', $empleadosConAsistencia)
            ->whereNotIn('id', $empleadosAsignados)
            ->get();

        return view('admin.schedulings.editModule', compact('scheduling', 'reasons', 'shifts', 'vehicles', 'personalNoAsistido', 'employeeGroup', 'personalDisponible'));
    }

    public function getContent(string $shiftId)
    {
        $employeeGroups = EmployeeGroup::with(['conductors', 'helpers'])
            ->where('shift_id', $shiftId)
            ->get();

        $vehicles = Vehicle::all();
        $zones = Zone::all();
        $shift = Shift::findOrFail($shiftId);

        $conductorType = EmployeeType::whereRaw('LOWER(name) = ?', ['conductor'])->first();
        $helperType = EmployeeType::whereRaw('LOWER(name) = ?', ['ayudante'])->first();
        $employeesConductor = $conductorType
            ? Employee::where('type_id', $conductorType->id)
                ->whereHas('contracts', function($query) {
                    $query->where('is_active', 1);
                })->get()
            : collect();

        $employeesAyudantes = $helperType
            ? Employee::where('type_id', $helperType->id)
                ->whereHas('contracts', function($query) {
                    $query->where('is_active', 1);
                })->get()
            : collect();

        return view('admin.schedulings.templantes.form', compact(
            'shiftId',
            'employeeGroups',
            'vehicles',
            'zones',
            'employeesConductor',
            'employeesAyudantes',
            'shift'
        ));
    }


    public function AddChangeScheduling(Request $request)
    {
        try {
            DB::beginTransaction();
            $changes = is_array($request->changes) ? $request->changes : json_decode($request->changes, true);
            $id_scheduling = $request->scheduling_id;
            $id_shift = Reason::whereRaw('LOWER(name) LIKE ?', ['%turno%'])->first()->id;
            $id_vehicle = Reason::whereRaw('LOWER(name) LIKE ?', ['%vehiculo%'])->first()->id;
            $id_employee = Reason::whereRaw('LOWER(name) LIKE ?', ['%personal%'])->first()->id;
            foreach ($changes as $change) {
                switch ($change['tipo']) {
                    case 'Turno':
                        Change::create([
                            'scheduling_id' => $id_scheduling,
                            'new_shift_id' => $change['id_nuevo'],
                            'reason_id' => $id_shift,
                            'change_date' => now(),
                            'old_shift_id' => $change['id_anterior'],
                            'notes' => $change['nota'],
                        ]);
                        $scheduling = Scheduling::findOrFail($id_scheduling);
                        $scheduling->update([
                            'shift_id' => $change['id_nuevo'],
                            'status' => 3
                        ]);

                        break;
                    case 'Vehiculo':
                        Change::create([
                            'scheduling_id' => $id_scheduling,
                            'new_vehicle_id' => $change['id_nuevo'],
                            'reason_id' => $id_vehicle,
                            'change_date' => now(),
                            'old_vehicle_id' => $change['id_anterior'],
                            'notes' => $change['nota'],
                        ]);

                        $scheduling = Scheduling::findOrFail($id_scheduling);
                        $scheduling->update([
                            'vehicle_id' => $change['id_nuevo'],
                        ]);

                        break;
                    case 'Personal':
                        Change::create([
                            'scheduling_id' => $id_scheduling,
                            'new_employee_id' => $change['id_nuevo'],
                            'reason_id' => $id_employee,
                            'change_date' => now(),
                            'old_employee_id' => $change['id_anterior'],
                            'notes' => $change['nota'],
                        ]);

                        $groupDetail = Groupdetail::where('scheduling_id', $id_scheduling)
                            ->where('employee_id', $change['id_anterior'])
                            ->first();

                        if ($groupDetail) {
                            $groupDetail->update([
                                'employee_id' => $change['id_nuevo']
                            ]);
                        }

                        break;
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Cambio agregado correctamente.'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ocurrió un error al guardar los cambios.' . $th->getMessage()
            ], 500);
        }
    }

    public function module()
    {
        $shifts = Shift::all();
        return view('admin.schedulings.module',compact('shifts'));
    }

   public function getDatascheduling(Request $request)
    {
        $shiftId = $request->turn;  // El turno seleccionado
        $date = $request->date;  // La fecha seleccionada
        $date = Carbon::parse($date)->format('Y-m-d');  // Convertir la fecha al formato adecuado

        // Obtener todos los schedulings con su relación con 'groupdetail'
        $schedulings = Scheduling::with('groupdetail')  // Cargar groupdetails relacionados
            ->where('shift_id', $shiftId)
            ->where('date', $date)
            ->get();

        // Inicializamos los contadores y las zonas
        $countAttendance = 0;
        $completedGroups = 0;
        $availableSupport = 0;
        $missing = 0;
        $zonas = [];
        $trueofalse = [];

        $availableSupport = Attendance::whereDate('attendance_date', $date)  // Filtramos por fecha de asistencia
        ->whereNotIn('employee_id', function($query) use ($shiftId, $date) {
            // Subconsulta para obtener los empleados asignados a un grupo en el turno y fecha específica
            $query->select('gd.employee_id')
                ->from('groupdetails as gd')
                ->join('schedulings as s', 'gd.scheduling_id', '=', 's.id')
                ->whereDate('s.date', $date)  // Filtramos por fecha
                ->where('s.shift_id', $shiftId);  // Filtramos por turno
        })
        ->count();

        // Recorrer todos los schedulings y calcular los datos
        foreach ($schedulings as $scheduling) {
            // 1. Contar los asistentes: Personas que están registradas en 'groupdetails' y tienen asistencia en 'attendance'
            $attendedMembers = Attendance::whereIn('employee_id', $scheduling->groupdetail->pluck('employee_id'))
                ->whereDate('attendance_date', $date)
                ->count();
            $countAttendance += $attendedMembers;  // Acumulamos el conteo de asistentes

            // 2. Contar los grupos completos: Un grupo se considera completo cuando todos los miembros tienen asistencia
            $totalGroupMembers = $scheduling->groupdetail->count();  // Número total de empleados en este grupo

            // Verificar si todos los empleados del grupo tienen asistencia
            $groupComplete = true;  // Suponemos que el grupo está completo

            foreach ($scheduling->groupdetail as $groupDetail) {
                $attendance = Attendance::where('employee_id', $groupDetail->employee_id)
                    ->whereDate('attendance_date', $date)
                    ->first();
                $trueofalse[] = $groupDetail;  // Guardamos si el empleado tiene asistencia o no
                // Si algún miembro no tiene asistencia, marcamos el grupo como incompleto
                if (!$attendance) {
                    $groupComplete = false;
                    break;
                }
            }

            // Si el grupo está completo, lo contamos como un grupo completo
            if ($groupComplete) {
                $completedGroups++;
            }



            // 4. Contar los faltantes: Miembros que no tienen una entrada en Attendance para ese día
            foreach ($scheduling->groupdetail as $groupDetail) {
                $attendance = Attendance::where('employee_id', $groupDetail->employee_id)
                    ->whereDate('attendance_date', $date)
                    ->first();

                if (!$attendance) {
                    $missing++;  // Si no tiene asistencia para esa fecha, lo contamos como faltante
                }
            }

            // 5. Obtener la zona del grupo y marcar si está completo o no
            $groupEmployee = EmployeeGroup::where('id', $scheduling->group_id)
                ->select('zone_id')
                ->first();

            if ($groupEmployee) {
                $zone = Zone::where('id', $groupEmployee->zone_id)
                    ->select('id', 'name')
                    ->first();

                // Si la zona no está repetida, la agregamos al array
                if ($zone && !in_array($zone, $zonas)) {
                    // Verificar si el grupo está completo con asistencia para marcar la zona como 'completa' o 'incompleta'
                    $zoneStatus = $groupComplete ? 'completa' : 'incompleta';

                    $zonas[] = [
                        'id' => $zone->id,
                        'scheduling_id' => $scheduling->id, // Agregar el ID de la programación
                        'name' => $zone->name,
                        'status' => $zoneStatus // Asignar el estado de la zona
                    ];
                }
            }
        }

        // Retornar los resultados
        return response()->json([
            'countAttendance' => $countAttendance,
            'completedGroups' => $completedGroups,
            'availableSupport' => $availableSupport,  // Pasamos los schedulings para que se puedan usar en la vista
            'missing' => $missing,
            'zonas' => $zonas,  // Pasamos las zonas con su estado
        ]);
    }

    public function createOne(Request $request)
    {
        $employeeGroups = EmployeeGroup::all();

        if ($request->ajax()) {
            // Si la petición es AJAX, devolvemos los datos correspondientes según el grupo
            $groupId = $request->input('employeegroups_id');
            $group = EmployeeGroup::with('shift','vehicle','zone','configgroup','configgroup.employee','configgroup.employee.employeeType')->find($groupId);
            $conductor = EmployeeType::whereRaw('LOWER(name) = ?', ['conductor'])->first()?->id ?? null;
            $ayudante = EmployeeType::whereRaw('LOWER(name) = ?', ['ayudante'])->first()?->id ?? null;
            $employeesConductor = Employee::where('type_id', $conductor)
                                ->whereHas('contracts', function($query) {
                                    $query->where('is_active', 1);
                                })
                                ->get();
            $employeesAyudantes = Employee::where('type_id', $ayudante)
                                    ->whereHas('contracts', function($query) {
                                                            $query->where('is_active', 1);
                                                        })
                                    ->get();
            // Parsear los días de trabajo del grupo
            $diasTrabajo = $group ? explode(',', $group->days) : [];

            return response()->json([
                'group' => $group,
                'diasTrabajo' => $diasTrabajo,
                'employeesConductor'=> $employeesConductor,
                'employeesAyudantes' => $employeesAyudantes// Pasar los días de trabajo seleccionados
            ]);
        }

        return view('admin.schedulings.createOne', compact('employeeGroups'));
    }


    public function storeOne(Request $request)
    {
        try {
            if (!$request->start_date) {
                return response()->json([
                    'message' => 'La fecha de inicio es requerida'
                ], 400);
            }

            DB::beginTransaction();

            $helpers = $request->helpers ?? [];
            $employeeGroup = EmployeeGroup::find($request->employee_group_id);
            $end_date = $request->end_date ?? $request->start_date;

            if (!$employeeGroup) {
                return response()->json([
                    'message' => 'El grupo de empleados no existe'
                ], 400);
            }

            // VALIDACIÓN DE VACACIONES - AGREGAR AQUÍ
            $driverVacation = $this->checkVacation($request->driver_id, $request->start_date, $end_date);
            if ($driverVacation) {
                $employee = Employee::find($request->driver_id);
                DB::rollBack();
                return response()->json([
                    'message' => "El conductor {$employee->names} {$employee->lastnames} tiene vacaciones aprobadas del " .
                                Carbon::parse($driverVacation->request_date)->format('d/m/Y') . " al " .
                                Carbon::parse($driverVacation->end_date)->format('d/m/Y')
                ], 400);
            }

            // Validar vacaciones de ayudantes
            foreach ($helpers as $helperId) {
                $helperVacation = $this->checkVacation($helperId, $request->start_date, $end_date);
                if ($helperVacation) {
                    $employee = Employee::find($helperId);
                    DB::rollBack();
                    return response()->json([
                        'message' => "El ayudante {$employee->names} {$employee->lastnames} tiene vacaciones aprobadas del " .
                                    Carbon::parse($helperVacation->request_date)->format('d/m/Y') . " al " .
                                    Carbon::parse($helperVacation->end_date)->format('d/m/Y')
                    ], 400);
                }
            }
            // FIN DE VALIDACIÓN DE VACACIONES

            $groupDays = $request->days ?? [];
            $daysOfWeek = [
                'Lunes' => Carbon::MONDAY,
                'Martes' => Carbon::TUESDAY,
                'Miércoles' => Carbon::WEDNESDAY,
                'Jueves' => Carbon::THURSDAY,
                'Viernes' => Carbon::FRIDAY,
                'Sábado' => Carbon::SATURDAY,
                'Domingo' => Carbon::SUNDAY,
            ];


            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($end_date);
            $errores = [];

            if ($request->end_date) {
                // CREACIÓN CON RANGO DE FECHAS
                while ($startDate->lte($endDate)) {
                    if (in_array($startDate->dayOfWeek, array_map(function($day) use ($daysOfWeek) {
                        return $daysOfWeek[$day];
                    }, $groupDays))) {

                        $dateStr = $startDate->toDateString();

                        // Validaciones completas
                        $validations = $this->runAllValidations(
                            $request->driver_id,
                            $helpers,
                            $employeeGroup->vehicle_id,
                            $employeeGroup->shift_id,
                            $employeeGroup->zone_id,
                            $dateStr,
                            $employeeGroup->name,
                            $end_date
                        );

                        if (!$validations['valid']) {
                            $errores = array_merge($errores, $validations['errors']);
                            $startDate->addDay();
                            continue;
                        }

                        // Crear scheduling
                        $scheduling = Scheduling::create([
                            'date' => $dateStr,
                            'group_id' => $employeeGroup->id,
                            'shift_id' => $employeeGroup->shift_id,
                            'vehicle_id' => $employeeGroup->vehicle_id,
                            'zone_id' => $employeeGroup->zone_id,
                            'notes' => '',
                            'status' => 1,
                        ]);

                        Groupdetail::create([
                            'employee_id' => $request->driver_id,
                            'scheduling_id' => $scheduling->id,
                        ]);

                        foreach ($helpers as $helper) {
                            Groupdetail::create([
                                'employee_id' => $helper,
                                'scheduling_id' => $scheduling->id,
                            ]);
                        }
                    }

                    $startDate->addDay();
                }
            } else {
                // CREACIÓN DE UN SOLO DÍA
                $dateStr = $startDate->toDateString();

                $validations = $this->runAllValidations(
                    $request->driver_id,
                    $helpers,
                    $employeeGroup->vehicle_id,
                    $employeeGroup->shift_id,
                    $employeeGroup->zone_id,
                    $dateStr,
                    $employeeGroup->name,
                    null
                );

                if (!$validations['valid']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Errores de validación',
                        'errors' => $validations['errors']
                    ], 400);
                }

                $scheduling = Scheduling::create([
                    'date' => $dateStr,
                    'group_id' => $employeeGroup->id,
                    'shift_id' => $employeeGroup->shift_id,
                    'vehicle_id' => $employeeGroup->vehicle_id,
                    'zone_id' => $employeeGroup->zone_id,
                    'notes' => '',
                    'status' => 1,
                ]);

                Groupdetail::create([
                    'employee_id' => $request->driver_id,
                    'scheduling_id' => $scheduling->id,
                ]);

                foreach ($helpers as $helper) {
                    Groupdetail::create([
                        'employee_id' => $helper,
                        'scheduling_id' => $scheduling->id,
                    ]);
                }
            }

            DB::commit();

            $response = ['success' => 'Programación creada correctamente.'];
            if (!empty($errores)) {
                $response['warnings'] = $errores;
            }

            return response()->json($response, 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear la programación: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Método helper para ejecutar todas las validaciones
     */
    private function runAllValidations($driverId, $helpers, $vehicleId, $shiftId, $zoneId, $date, $groupName, $endDate = null)
{
    $errores = [];

    // VALIDAR VACACIONES DEL CONDUCTOR
    $driverVacation = $this->validateNoVacation($driverId, $date, $endDate);
    if (!$driverVacation['valid']) {
        $errores[] = "[{$groupName}] {$driverVacation['message']}";
    }

    // Validar conductor
    $driverContract = $this->validateActiveContract($driverId, $date);
    if (!$driverContract['valid']) {
        $errores[] = "[{$groupName}] {$driverContract['message']}";
    }

    $driverAvailability = $this->validateEmployeeAvailability($driverId, $date, $shiftId);
    if (!$driverAvailability['valid']) {
        $errores[] = "[{$groupName}] {$driverAvailability['message']}";
    }

    // Validar ayudantes
    foreach ($helpers as $helperId) {
        // VALIDAR VACACIONES DE AYUDANTE
        $helperVacation = $this->validateNoVacation($helperId, $date, $endDate);
        if (!$helperVacation['valid']) {
            $errores[] = "[{$groupName}] {$helperVacation['message']}";
        }

        $helperContract = $this->validateActiveContract($helperId, $date);
        if (!$helperContract['valid']) {
            $errores[] = "[{$groupName}] {$helperContract['message']}";
        }

        $helperAvailability = $this->validateEmployeeAvailability($helperId, $date, $shiftId);
        if (!$helperAvailability['valid']) {
            $errores[] = "[{$groupName}] {$helperAvailability['message']}";
        }
    }

    // Validar vehículo
    $vehicleValidation = $this->validateVehicleAvailability($vehicleId, $date, $shiftId);
    if (!$vehicleValidation['valid']) {
        $errores[] = "[{$groupName}] {$vehicleValidation['message']}";
    }

    // Validar duplicados
    $duplicateValidation = $this->validateNoDuplicateScheduling($date, $shiftId, $vehicleId, $driverId, $helpers);
    if (!$duplicateValidation['valid']) {
        $errores[] = "[{$groupName}] {$duplicateValidation['message']}";
    }

    return [
        'valid' => empty($errores),
        'errors' => $errores
    ];
}

    public function validationVacations(Request $request){
        $ListaNoDisponibles = [];
        $ListaVacaciones = [];
        $helpers= $request->helpers;
        $end_date = null;
        $start_date = $request->start_date;

        if($request->end_date) {
            $end_date = $request->end_date;
        } else {
            $end_date = $request->start_date; // Si no hay end_date, usamos start_date
        }


        foreach( $helpers as $helper) {
            $vacation = $this->checkVacation($helper, $start_date, $end_date);

            if($vacation){
                array_push($ListaNoDisponibles, $vacation->employee_id);
                array_push($ListaVacaciones, $vacation);
            }
        }

        return response()->json([
            'no_disponibles' => $ListaNoDisponibles,
            'vacaciones' => $ListaVacaciones
        ])->setStatusCode(200, 'OK', [
            'Content-Type' => 'application/json'
        ]);
    }




    /**
 * Ejecutar todas las validaciones incluyendo vacaciones
 * (versión completa para programación masiva)
 */
private function runAllValidationsWithVacations($driverId, $helpers, $vehicleId, $shiftId, $zoneId, $date, $groupName, $endDate = null)
{
    $errores = [];

    // 1. VALIDAR VACACIONES DEL CONDUCTOR
    $driverVacation = $this->checkVacation($driverId, $date, $endDate ?? $date);
    if ($driverVacation) {
        $employee = Employee::find($driverId);
        $errores[] = "[{$groupName} - {$date}] El conductor {$employee->names} {$employee->lastnames} tiene vacaciones del " .
                    Carbon::parse($driverVacation->request_date)->format('d/m/Y') . " al " .
                    Carbon::parse($driverVacation->end_date)->format('d/m/Y');
    }

    // 2. VALIDAR VACACIONES DE AYUDANTES
    foreach ($helpers as $helperId) {
        $helperVacation = $this->checkVacation($helperId, $date, $endDate ?? $date);
        if ($helperVacation) {
            $employee = Employee::find($helperId);
            $errores[] = "[{$groupName} - {$date}] El ayudante {$employee->names} {$employee->lastnames} tiene vacaciones del " .
                        Carbon::parse($helperVacation->request_date)->format('d/m/Y') . " al " .
                        Carbon::parse($helperVacation->end_date)->format('d/m/Y');
        }
    }

    // 3. VALIDAR CONTRATO CONDUCTOR
    $driverContract = $this->validateActiveContract($driverId, $date);
    if (!$driverContract['valid']) {
        $errores[] = "[{$groupName} - {$date}] {$driverContract['message']}";
    }

    // 4. VALIDAR DISPONIBILIDAD CONDUCTOR
    $driverAvailability = $this->validateEmployeeAvailability($driverId, $date, $shiftId);
    if (!$driverAvailability['valid']) {
        $errores[] = "[{$groupName} - {$date}] {$driverAvailability['message']}";
    }

    // 5. VALIDAR AYUDANTES
    foreach ($helpers as $helperId) {
        $helperContract = $this->validateActiveContract($helperId, $date);
        if (!$helperContract['valid']) {
            $errores[] = "[{$groupName} - {$date}] {$helperContract['message']}";
        }

        $helperAvailability = $this->validateEmployeeAvailability($helperId, $date, $shiftId);
        if (!$helperAvailability['valid']) {
            $errores[] = "[{$groupName} - {$date}] {$helperAvailability['message']}";
        }
    }

    // 6. VALIDAR VEHÍCULO
    $vehicleValidation = $this->validateVehicleAvailability($vehicleId, $date, $shiftId);
    if (!$vehicleValidation['valid']) {
        $errores[] = "[{$groupName} - {$date}] {$vehicleValidation['message']}";
    }

    // 7. VALIDAR DUPLICADOS
    $duplicateValidation = $this->validateNoDuplicateScheduling($date, $shiftId, $vehicleId, $driverId, $helpers);
    if (!$duplicateValidation['valid']) {
        $errores[] = "[{$groupName} - {$date}] {$duplicateValidation['message']}";
    }

    return [
        'valid' => empty($errores),
        'errors' => $errores
    ];
}





    /**
 * Validar que el empleado NO esté de vacaciones
 */
private function validateNoVacation($employeeId, $date, $endDate = null)
{
    $employee = Employee::find($employeeId);

    if (!$employee) {
        return [
            'valid' => false,
            'message' => "El empleado no existe"
        ];
    }

    $vacation = $this->checkVacation($employeeId, $date, $endDate ?? $date);

    if ($vacation) {
        return [
            'valid' => false,
            'message' => "El empleado {$employee->names} {$employee->lastnames} tiene vacaciones aprobadas del " .
                        Carbon::parse($vacation->request_date)->format('d/m/Y') . " al " .
                        Carbon::parse($vacation->end_date)->format('d/m/Y')
        ];
    }

    return ['valid' => true];
}






    public function validationDuplicate($fecha,$zona,$shift){
        $isDuplicate = false;
        $scheduling = Scheduling::where('date', $fecha)
            ->where('zone_id', $zona)
            ->where('shift_id', $shift)
            ->first();

        if ($scheduling) {
            $isDuplicate = true;
        }

        return $isDuplicate;
    }










    /**
     * Validar que el empleado tenga contrato activo
     */
    private function validateActiveContract($employeeId, $date)
    {
        $employee = Employee::with(['contracts' => function($query) use ($date) {
            $query->where('is_active', 1)
                ->where('start_date', '<=', $date)
                ->where(function($q) use ($date) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $date);
                });
        }])->find($employeeId);

        if (!$employee || $employee->contracts->isEmpty()) {
            return [
                'valid' => false,
                'message' => "El empleado {$employee->names} {$employee->lastnames} no tiene contrato activo para la fecha seleccionada"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validar disponibilidad del vehículo
     * - No debe estar en mantenimiento
     * - No debe estar programado en otro turno/zona el mismo día
     */
    private function validateVehicleAvailability($vehicleId, $date, $shiftId, $excludeSchedulingId = null)
    {
        $vehicle = Vehicle::find($vehicleId);

        if (!$vehicle) {
            return [
                'valid' => false,
                'message' => "El vehículo no existe"
            ];
        }

        // 1. Verificar si está en mantenimiento ese día
        $inMaintenance = DB::table('maintenance_activities as ma')
            ->join('maintenance_schedules as ms', 'ma.schedule_id', '=', 'ms.id')
            ->where('ms.vehicle_id', $vehicleId)
            ->whereDate('ma.activity_date', $date)
            ->where('ma.completed', 0) // No completado = aún en mantenimiento
            ->exists();

        if ($inMaintenance) {
            return [
                'valid' => false,
                'message' => "El vehículo {$vehicle->code} tiene mantenimiento programado para el {$date}"
            ];
        }

        // 2. Verificar si ya está programado en otro scheduling el mismo día/turno
        $query = Scheduling::where('vehicle_id', $vehicleId)
            ->whereDate('date', $date)
            ->where('shift_id', $shiftId)
            ->whereIn('status', [1, 2]); // Programado o Completado/Iniciado

        if ($excludeSchedulingId) {
            $query->where('id', '!=', $excludeSchedulingId);
        }

        if ($query->exists()) {
            return [
                'valid' => false,
                'message' => "El vehículo {$vehicle->code} ya está programado para el turno seleccionado en la fecha {$date}"
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validar que no exista duplicado de programación
     * Según requisito: "No debe permitir crear 2 o más programaciones que coincidan
     * en Turno, Vehículo, Conductor y Ayudantes"
     */
    private function validateNoDuplicateScheduling($date, $shiftId, $vehicleId, $driverId, $helpers, $excludeSchedulingId = null)
    {
        // 1. Buscar schedulings del mismo turno, fecha y vehículo
        $query = Scheduling::where('date', $date)
            ->where('shift_id', $shiftId)
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', [1, 2]); // Solo activos

        if ($excludeSchedulingId) {
            $query->where('id', '!=', $excludeSchedulingId);
        }

        $existingSchedulings = $query->get();

        if ($existingSchedulings->isEmpty()) {
            return ['valid' => true];
        }

        // 2. Verificar si alguno tiene el mismo personal
        foreach ($existingSchedulings as $scheduling) {
            $existingEmployees = $scheduling->groupdetail->pluck('employee_id')->toArray();
            $newEmployees = array_merge([$driverId], $helpers);

            // Si todos los empleados coinciden, es duplicado
            if (count($existingEmployees) === count($newEmployees) &&
                empty(array_diff($existingEmployees, $newEmployees))) {
                return [
                    'valid' => false,
                    'message' => "Ya existe una programación idéntica para esta fecha, turno, vehículo y personal"
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Validar que el personal no esté programado en otro turno/zona el mismo día
     */
    private function validateEmployeeAvailability($employeeId, $date, $shiftId, $excludeSchedulingId = null)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return [
                'valid' => false,
                'message' => "El empleado no existe"
            ];
        }

        // Buscar si el empleado ya está en otro scheduling ese día/turno
        $query = Groupdetail::whereHas('scheduling', function($q) use ($date, $shiftId, $excludeSchedulingId) {
            $q->whereDate('date', $date)
            ->where('shift_id', $shiftId)
            ->whereIn('status', [1, 2]);

            if ($excludeSchedulingId) {
                $q->where('id', '!=', $excludeSchedulingId);
            }
        })->where('employee_id', $employeeId);

        if ($query->exists()) {
            return [
                'valid' => false,
                'message' => "El empleado {$employee->names} {$employee->lastnames} ya está programado en otro grupo para esta fecha y turno"
            ];
        }

        return ['valid' => true];
    }





    /**
 * Validar disponibilidad completa ANTES de guardar
 */
public function validateAvailability(Request $request)
{
    try {
        if (!$request->start_date) {
            return response()->json([
                'message' => 'La fecha de inicio es requerida'
            ], 400);
        }

        $end_date = $request->end_date ?? $request->start_date;
        $todosLosErrores = [];

        // Validar TODO (igual que en store, pero sin guardar)
        foreach ($request->groups as $group) {
            $employeeGroup = EmployeeGroup::find($group['employee_group_id']);

            if (!$employeeGroup) {
                $todosLosErrores[] = "El grupo {$group['employee_group_id']} no existe";
                continue;
            }

            $groupDays = explode(',', $employeeGroup->days);
            $daysOfWeek = [
                'Lunes' => Carbon::MONDAY,
                'Martes' => Carbon::TUESDAY,
                'Miércoles' => Carbon::WEDNESDAY,
                'Jueves' => Carbon::THURSDAY,
                'Viernes' => Carbon::FRIDAY,
                'Sábado' => Carbon::SATURDAY,
                'Domingo' => Carbon::SUNDAY,
            ];

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($end_date);
            $helpers = $group['helpers'] ?? [];

            while ($startDate->lte($endDate)) {
                if (in_array($startDate->dayOfWeek, array_map(function($day) use ($daysOfWeek) {
                    return $daysOfWeek[$day];
                }, $groupDays))) {

                    $dateStr = $startDate->toDateString();

                    $validations = $this->runAllValidationsWithVacations(
                        $group['driver_id'],
                        $helpers,
                        $employeeGroup->vehicle_id,
                        $employeeGroup->shift_id,
                        $employeeGroup->zone_id,
                        $dateStr,
                        $employeeGroup->name,
                        $end_date
                    );

                    if (!$validations['valid']) {
                        $todosLosErrores = array_merge($todosLosErrores, $validations['errors']);
                    }
                }

                $startDate->addDay();
            }

            $startDate = Carbon::parse($request->start_date);
        }

        // Si hay errores, agrupar y retornar
        if (!empty($todosLosErrores)) {
            $erroresAgrupados = $this->agruparErrores($todosLosErrores);

            return response()->json([
                'valid' => false,
                'message' => 'Se encontraron los siguientes problemas:',
                'errors' => $erroresAgrupados
            ], 400);
        }

        // Todo OK
        return response()->json([
            'valid' => true,
            'message' => 'Todas las validaciones pasaron correctamente. Puede proceder a registrar la programación.'
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Error al validar: ' . $th->getMessage()
        ], 500);
    }
}





}
