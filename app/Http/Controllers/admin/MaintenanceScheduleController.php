<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Maintenance;
use App\Models\MaintenanceActivity;
use App\Models\Vehicle;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use Carbon\Carbon;


class MaintenanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $maintenance_id = $request->maintenance_id;

        $schedules = MaintenanceSchedule::select(
            'maintenance_schedules.id',
            'maintenance_schedules.day_of_week',
            'maintenance_schedules.maintenance_type',
            'maintenance_schedules.start_time',
            'maintenance_schedules.end_time',
            'maintenance_schedules.status',
            'v.name as vehicle_name'
        )
            ->selectRaw("CONCAT(e.names, ' ', e.lastnames) as responsible_name")
            ->join('vehicles as v', 'maintenance_schedules.vehicle_id', '=', 'v.id')
            ->join('employees as e', 'maintenance_schedules.responsible_id', '=', 'e.id')
            ->where('maintenance_schedules.maintenance_id', $maintenance_id);

        if ($request->ajax()) {
            return DataTables::of($schedules)
                ->addColumn("status_badge", function ($schedule) {
                    if ($schedule->status == 1) {
                        return '<span class="badge badge-success">Activo</span>';
                    } else {
                        return '<span class="badge badge-danger">Inactivo</span>';
                    }
                })
                ->addColumn("activities", function ($schedule) {
                    return '<button class="btn btn-light btn-sm btnActivities" data-id="' . $schedule->id . '">
                            <i class="fas fa-car"></i>
                        </button>';
                })
                ->addColumn("edit", function ($schedule) {
                    return '<button class="btn btn-secondary btn-sm btnEditSchedule" data-id="' . $schedule->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($schedule) {
                    return '<form action="' . route('admin.schedules.destroy', $schedule) . '" method="POST" class="frmDeleteSchedule">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['status_badge', 'activities', 'edit', 'delete'])
                ->make(true);
        } else {
            $maintenance = Maintenance::find($maintenance_id);
            return view('admin.maintenances.schedules.index', compact('schedules', 'maintenance'));
        }
    }

    public function create(Request $request)
    {
        $maintenance_id = $request->maintenance_id;
        $maintenance = Maintenance::find($maintenance_id);
        $vehicles = Vehicle::where('status', 1)->pluck('name', 'id');
        $employees = Employee::where('status', true)
        ->get()
        ->mapWithKeys(function ($employee) {
            return [$employee->id => $employee->names . ' ' . $employee->lastnames];
        });

        return view('admin.maintenances.schedules.create', compact('maintenance', 'vehicles', 'employees'));
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            "maintenance_id" => "required|exists:maintenances,id",
            "vehicle_id" => "required|exists:vehicles,id",
            "responsible_id" => "required|exists:employees,id",
            "maintenance_type" => "required|in:PREVENTIVO,LIMPIEZA,REPARACION",
            "day_of_week" => "required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO",
            "start_time" => "required",
            "end_time" => "required|after:start_time"
        ]);

        // Validar solapamiento (código anterior...)
        $overlap = MaintenanceSchedule::where('maintenance_id', $request->maintenance_id)
            ->where('vehicle_id', $request->vehicle_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>', $request->start_time);
                })
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                            ->where('end_time', '>=', $request->end_time);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '>=', $request->start_time)
                            ->where('end_time', '<=', $request->end_time);
                    });
            })->exists();

        if ($overlap) {
            return response()->json([
                "message" => "El horario se solapa con otro horario existente para el mismo vehículo y día"
            ], 400);
        }

        // Crear el horario
        $schedule = MaintenanceSchedule::create($request->all());

        // GENERAR AUTOMÁTICAMENTE LOS DÍAS
        $this->generateScheduleDays($schedule);

        return response()->json(["message" => "Horario registrado correctamente"], 200);

    } catch (\Exception $e) {
        return response()->json([
            "message" => "Error al registrar horario",
            "error" => $e->getMessage()
        ], 500);
    }
}

// MÉTODO PARA GENERAR LOS DÍAS AUTOMÁTICAMENTE
private function generateScheduleDays($schedule)
{
    $maintenance = $schedule->maintenance;
    $startDate = Carbon::parse($maintenance->start_date);
    $endDate = Carbon::parse($maintenance->end_date);

    // Mapeo de días en español a números
    $dayOfWeekMap = [
        'LUNES' => Carbon::MONDAY,
        'MARTES' => Carbon::TUESDAY,
        'MIERCOLES' => Carbon::WEDNESDAY,
        'JUEVES' => Carbon::THURSDAY,
        'VIERNES' => Carbon::FRIDAY,
        'SABADO' => Carbon::SATURDAY,
        'DOMINGO' => Carbon::SUNDAY
    ];

    $targetDayOfWeek = $dayOfWeekMap[$schedule->day_of_week];

    // Encontrar el primer día que coincida
    $currentDate = $startDate->copy();
    if ($currentDate->dayOfWeek !== $targetDayOfWeek) {
        $currentDate->next($targetDayOfWeek);
    }

    // Generar todos los días que coincidan con el día de la semana
    while ($currentDate->lte($endDate)) {
        MaintenanceActivity::create([
            'schedule_id' => $schedule->id,
            'activity_date' => $currentDate->format('Y-m-d'),
            'observation' => null,
            'image' => null,
            'completed' => false,
            'status' => 1
        ]);

        $currentDate->addWeek();
    }
}

    public function edit(string $id)
    {
        $schedule = MaintenanceSchedule::find($id);
        $maintenance = $schedule->maintenance;
        $vehicles = Vehicle::where('status', 1)->pluck('name', 'id');
        $employees = Employee::where('status', true)
        ->get()
        ->mapWithKeys(function ($employee) {
            return [$employee->id => $employee->names . ' ' . $employee->lastnames];
        });

        return view('admin.maintenances.schedules.edit', compact('schedule', 'maintenance', 'vehicles', 'employees'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $schedule = MaintenanceSchedule::find($id);

            $request->validate([
                "vehicle_id" => "required|exists:vehicles,id",
                "responsible_id" => "required|exists:employees,id",
                "maintenance_type" => "required|in:PREVENTIVO,LIMPIEZA,REPARACION",
                "day_of_week" => "required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO",
                "start_time" => "required",
                "end_time" => "required|after:start_time"
            ]);

            // Validar solapamiento (código anterior...)
            $overlap = MaintenanceSchedule::where('id', '!=', $id)
                ->where('maintenance_id', $schedule->maintenance_id)
                ->where('vehicle_id', $request->vehicle_id)
                ->where('day_of_week', $request->day_of_week)
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>', $request->start_time);
                    })
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_time', '<', $request->end_time)
                                ->where('end_time', '>=', $request->end_time);
                        })
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_time', '>=', $request->start_time)
                                ->where('end_time', '<=', $request->end_time);
                        });
                })->exists();

            if ($overlap) {
                return response()->json([
                    "message" => "El horario se solapa con otro horario existente para el mismo vehículo y día"
                ], 400);
            }

            $schedule->update($request->all());

            // REGENERAR LOS DÍAS SI CAMBIÓ EL DÍA DE LA SEMANA
            if ($schedule->wasChanged('day_of_week')) {
                // Eliminar días anteriores
                $schedule->activities()->delete();
                // Generar nuevos días
                $this->generateScheduleDays($schedule);
            }

            return response()->json(["message" => "Horario actualizado correctamente"], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al actualizar horario",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $schedule = MaintenanceSchedule::findOrFail($id);

            // Eliminar el horario y sus días (gracias al cascade en la migración)
            $schedule->delete();

            return response()->json(["message" => "Horario y sus días generados eliminados correctamente"], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al eliminar horario",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
