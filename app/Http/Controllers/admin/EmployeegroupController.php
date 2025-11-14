<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Configgroup;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Employeegroup;
use App\Models\EmployeeType;
use App\Models\Scheduling;
use App\Models\Vehicletype;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeegroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employeeGroups = Employeegroup::with('shift', 'vehicle', 'zone')
            ->withCount('configgroup')
            ->get();
            
            return DataTables::of($employeeGroups)
            
                ->addColumn('days', function ($employeeGroup) {
                    return $employeeGroup->days;
                })
                ->addColumn('shift', function ($employeeGroup) {
                    return $employeeGroup->shift->name;
                })
                ->addColumn('vehicle', function ($employeeGroup) {
                    return $employeeGroup->vehicle->code;
                })
                ->addColumn('zone', function ($employeeGroup) {
                    return $employeeGroup->zone->name;
                })
                ->addColumn('action', function ($employeeGroup) {
                    $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $employeeGroup->id . '">
                                    <i class="fas fa-edit"></i>
                                </button>';

                    if($employeeGroup->configgroup_count > 0){
                        $viewBtn = '<button class="btn btn-info btn-sm btnVer" id="' . $employeeGroup->id . '">
                                    <i class="fas fa-users"></i>
                                </button>';
                    }else{
                        $viewBtn = '';
                    }
                    
                    $deleteBtn = '<form class="delete d-inline" action="' . route('admin.employeegroups.destroy', $employeeGroup->id) . '" method="POST">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';
                    
                    return $editBtn . ' ' . $viewBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.employee-groups.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $zones = Zone::all();
        $shifts = Shift::all();
        $vehicles = Vehicle::all();
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
        return view('admin.employee-groups.create', compact('zones', 'shifts', 'vehicles', 'employeesConductor', 'employeesAyudantes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::transaction(function() use($request){
                $days = '';

                foreach ($request->days as $day) {
                    $days .= $day.',';
                }

                $days = substr($days, 0, -1);

                $employeegroup = Employeegroup::create([
                    'zone_id' => $request->zone_id,
                    'shift_id' => $request->shift_id,
                    'vehicle_id' => $request->vehicle_id,
                    'name'=>$request->name,
                    'days'=>$days,
                    'status'=>1,
                ]);

                if($request->driver_id){
                    Configgroup::create([
                        'employeegroup_id' => $employeegroup->id,
                        'employee_id' => $request->driver_id,
                    ]);
                }

                if($request->helpers && count($request->helpers) > 0){
                    foreach ($request->helpers as $helper) {
                        Configgroup::create([
                            'employeegroup_id' => $employeegroup->id,
                            'employee_id' => $helper,
                        ]);
                    }
                }
             
               
                return response()->json([
                    'message' => 'Grupo de personal creado exitosamente'
                ], 200);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear el grupo de personal: '.$th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employeeGroup = Employeegroup::with(['conductors', 'helpers'])->findOrFail($id);
        return view('admin.employee-groups.show', compact('employeeGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $zones = Zone::all();
        $shifts = Shift::all();
        $vehicles = Vehicle::all();
    
        // Obtén los tipos de empleado para conductor y ayudante
        $conductorType = EmployeeType::whereRaw('LOWER(name) = ?', ['conductor'])->first();
        $helperType = EmployeeType::whereRaw('LOWER(name) = ?', ['ayudante'])->first();
    
        // Todos los conductores y ayudantes disponibles (para el select)
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
       
        // El grupo con sus empleados asociados filtrados por tipo
        $employeeGroup = Employeegroup::with(['conductors', 'helpers'])->findOrFail($id);
        $driverId = optional($employeeGroup->conductors->first())->id;

        return view('admin.employee-groups.edit', compact(
            'zones',
            'shifts',
            'vehicles',
            'employeesConductor',
            'employeesAyudantes',
            'employeeGroup',
            'driverId'
        ));
    }
    

    public function data(){
        $employeeGroups = Employeegroup::with('shift', 'vehicle', 'zone')
            ->withCount('configgroup')
            ->get();
        
        return response()->json($employeeGroups);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function() use($request, $id){
                $employeeGroup = Employeegroup::findOrFail($id);
                $days = '';

                foreach ($request->days as $day) {
                    $days .= $day.',';
                }

                $days = substr($days, 0, -1);

                $employeeGroup->update([
                    'zone_id' => $request->zone_id,
                    'shift_id' => $request->shift_id,
                    'vehicle_id' => $request->vehicle_id,
                    'name'=>$request->name,
                    'days'=>$days,
                    'status'=>1,
                ]);
                
                if($request->driver_id){
                    Configgroup::where('employeegroup_id', $id)->delete();
                    Configgroup::create([
                        'employeegroup_id'=>$employeeGroup->id,
                        'employee_id'=>$request->driver_id,
                    ]);

                    if($request->helpers && count($request->helpers) > 0){
                        foreach ($request->helpers as $ayudante) {
                            Configgroup::create([
                                'employeegroup_id'=>$employeeGroup->id,
                                'employee_id'=>$ayudante,
                            ]);
                        }   
                    }
                }

                

                return response()->json([
                    'message' => 'Grupo de personal actualizado exitosamente'
                ], 200);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el grupo de personal: '.$th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $employeeGroup = Employeegroup::findOrFail($id);
            $schedulings = Scheduling::where('group_id', $id)->first();
            if ($schedulings) {
                return response()->json([
                    'message' => 'No se puede eliminar el grupo de personal porque tiene asignaciones de programación.'
                ], 400);
            }
            DB::beginTransaction();
            Configgroup::where('employeegroup_id', $id)->delete();
            $employeeGroup->delete();
            DB::commit();
            return response()->json([
                'message' => 'Grupo de personal eliminado exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar el grupo de personal: '.$th->getMessage()
            ], 500);
        }
    }

    public function vehiclechange(string $id){
        $vehicletypes = Vehicletype::all();
        $vehicles = Vehicle::all();
        $employeeGroup = Employeegroup::findOrFail($id);        
        return view('admin.employee-groups.vehiclechange', compact('vehicletypes', 'employeeGroup', 'vehicles'));
    }

    public function vehiclechangeupdate(Request $request, string $id){
        try {
            $employeeGroup = Employeegroup::findOrFail($id);
            $employeeGroup->update([
                'vehicle_id' => $request->vehicle_id,
            ]);
            return response()->json([
                'message' => 'Vehículo actualizado exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar el vehículo: '.$th->getMessage()
            ], 500);
        }
    }
}
