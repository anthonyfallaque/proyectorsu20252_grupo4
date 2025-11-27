<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Contract;
use App\Models\Attendance;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Exception;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $fechaActual = Carbon::now()->format('Y-m-d');
        if ($request->ajax()) {
            $query = Attendance::with('employee')->select([
                'id',
                'employee_id',
                'attendance_date',
                'status',
                'period',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->orderBy('employee_id')
            ->orderBy('created_at'); // Ordenar por fecha de creación para mantener orden temporal

            if($request->filled('start_date') && !$request->filled('end_date')){
                    $query->whereDate('attendance_date', '=', $request->start_date);
            }else{
                if ($request->filled('start_date')) {
                    $query->whereDate('attendance_date', '>=', $request->start_date);
                }

                if ($request->filled('end_date')) {
                    $query->whereDate('attendance_date', '<=', $request->end_date);
                }
            }

            $attendances = $query->get();


            return DataTables::of($attendances)
                ->addColumn('employee_dni', function ($attendance) {
                    return $attendance->employee ? $attendance->employee->dni : 'Sin empleado';
                })
                ->addColumn('employee_name', function ($attendance) {
                    return $attendance->employee ? $attendance->employee->names . ' ' . $attendance->employee->lastnames : 'Sin empleado';
                })
                ->addColumn('status_badge', function ($attendance) {
                    if($attendance->status == 1){
                        return '<span class="badge badge-success">Presente</span>';
                    }elseif($attendance->status == 2){
                        return '<span class="badge badge-primary">Justificado</span>';
                    }else{
                        return '<span class="badge badge-danger">Ausente</span>';
                    }
                })
                ->addColumn('status_period', function ($attendance) {
                   return $attendance->period == 0
                    ? '<span class="badge badge-info">Entrada</span>'
                    : '<span class="badge badge-danger">Salida</span>';
                })
                ->addColumn('created_at', function ($attendance) {
                    return Carbon::parse($attendance->created_at)->format('d/m/Y H:i:s');
                })
                ->addColumn('action', function ($attendance) {
                    $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $attendance->id . '">
                                    <i class="fas fa-edit"></i>
                                </button>';


                    return $editBtn ;
                })
                ->rawColumns(['action','status_period','status_badge'])
                ->make(true);
        }

        return view('admin.attendances.index', compact('fechaActual'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       $employees = Employee::whereHas('contracts', function($query) {
            $query->where('is_active', 1);
        })
        ->get();
        return view('admin.attendances.create', compact('employees'));
    }

    public function checkContract($id){
        return Contract::where('employee_id',$id)
        ->where('is_active',1)
        ->latest()
        ->first();
    }

    /**
     * FUNCIÓN: Determinar el periodo correcto basado en el historial del día
     */
    private function determineNextPeriod($employeeId, $attendanceDate)
    {
        // Obtener todas las asistencias del día ordenadas por hora de creación
        $todayAttendances = Attendance::where('employee_id', $employeeId)
            ->whereDate('attendance_date', $attendanceDate)
            ->orderBy('created_at')
            ->get();

        $count = $todayAttendances->count();

        // Si no hay asistencias, es la primera entrada
        if ($count == 0) {
            return 0; // Entrada
        }

        // Si ya hay 4 asistencias, no permitir más
        if ($count >= 4) {
            return null; // Indicador de que ya alcanzó el límite
        }

        // Obtener el último periodo registrado
        $lastPeriod = $todayAttendances->last()->period;

        // Alternar entre entrada (0) y salida (1)
        return $lastPeriod == 0 ? 1 : 0;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       try{
            $contract = $this->checkContract($request->employee_id);

            if(!$contract){
                return response()->json([
                    'message' => 'La persona no cuenta con un contrato activo.'
                ], 400);
            }

            $attendanceCount = Attendance::where('employee_id', $request->employee_id)
                ->where('attendance_date', $request->attendance_date)
                ->count();

            if($attendanceCount >= 4){
                return response()->json([
                    'message' => 'Límite de asistencias diarias alcanzadas.'
                ], 400);
            }

            // FUNCIÓN para determinar el periodo
            $period = $this->determineNextPeriod($request->employee_id, $request->attendance_date);

            if ($period === null) {
                return response()->json([
                    'message' => 'Límite de asistencias diarias alcanzadas.'
                ], 400);
            }

            Attendance::create([
                'employee_id' => $request->employee_id,
                'attendance_date' => $request->attendance_date,
                'period' => $period,
                'status' => 1,
                'notes' => $request->notes,
            ]);

            $periodLabel = $period == 0 ? 'Entrada' : 'Salida';

            return response()->json([
                'message' => "Asistencia creada exitosamente ({$periodLabel})."
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al crear la asistencia: '.$th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $attendance = Attendance::findOrFail($id);
        $employees = Employee::all();
        return view('admin.attendances.edit', compact('attendance', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{

            $attendance = Attendance::findOrFail($id);
            $attendance->update($request->all());
            return response()->json([
                'message' => 'Asistencia actualizada exitosamente.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar la asistencia: '.$th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $attendance = Attendance::findOrFail($id);
            $attendance->update([
                'deleted_at' => now()
            ]);

            return response()->json([
                'message' => 'Asistencia eliminada exitosamente.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar la asistencia: '.$th->getMessage()]);
        }
    }

    public function indexAttendance()
    {
        return view('attendances');
    }

    public function storeAttendance(Request $request)
    {
        $employee = Employee::where('dni', $request->dni)->first();
        if (!$employee) {
           return redirect()->back()->with('error', 'Datos incorrectos');
        }

        if (!Hash::check($request->password, $employee->password)) {
            return redirect()->back()->with('error', 'Datos incorrectos');
        }

        $attendanceCount = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', now()->toDateString())
            ->count();

        if($attendanceCount >= 4){
            return redirect()->back()->with('error', 'Límite de asistencias diarias alcanzadas');
        }

        // FUNCIÓN para determinar el periodo
        $period = $this->determineNextPeriod($employee->id, now()->toDateString());

        if ($period === null) {
            return redirect()->back()->with('error', 'Límite de asistencias diarias alcanzadas');
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'attendance_date' => now(),
            'period' => $period,
            'status' => 1
        ]);

        $periodLabel = $period == 0 ? 'Entrada' : 'Salida';

        return redirect()->back()->with('success', "Asistencia registrada correctamente ({$periodLabel})");
    }
}
