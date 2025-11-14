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
            ->orderBy('employee_id');

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
                    return Carbon::parse($attendance->created_at)->format('d/m/Y H:i:s');  // Cambia el formato según lo necesites
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
            $query->where('is_active', 1); // Filtra contratos activos
        })
        ->get();
        return view('admin.attendances.create', compact('employees'));
    }

    public function checkContract($id){
        return Contract::where('employee_id',$id)
        ->where('is_active',1)
        ->latest()
        ->first();;
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

            $attendance = Attendance::where('employee_id', $request->employee_id)->where('attendance_date', $request->attendance_date)->count();

            if($attendance >=4){
                return response()->json([
                    'message' => 'Limite de asistencias diarias alcanzadas.'
                ], 400);
            }

            if($attendance % 2 == 0){
                Attendance::create([
                    'employee_id'=>$request->employee_id,
                    'attendance_date'=>$request->attendance_date,
                    'period'=>0,
                    'status'=>1,
                    'notes'=>$request->notes,
                ]);
            }else{
                Attendance::create([
                    'employee_id'=>$request->employee_id,
                    'attendance_date'=>$request->attendance_date,
                    'period'=>1,
                    'status'=>1,
                    'notes'=>$request->notes,
                ]);
            }

            return response()->json([
                'message' => 'Asistencia creada exitosamente.'
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

        $attendance = Attendance::where('employee_id', $employee->id)->where('attendance_date', now()->toDateString())->count();

        if($attendance >=4){
            return redirect()->back()->with('error', 'Limite de asistencias diarias alcanzadas');
        }

        if($attendance % 2 == 0){
            Attendance::create([
                'employee_id'=>$employee->id,
                'attendance_date'=>now(),
                'period'=>0,
                'status'=>1
            ]);
        }else{
            Attendance::create([
                'employee_id'=>$employee->id,
                'attendance_date'=>now(),
                'period'=>1,
                'status'=>1
            ]);
        }



        return redirect()->back()->with('success', 'Asistencia registrada correctamente');

    }
}
