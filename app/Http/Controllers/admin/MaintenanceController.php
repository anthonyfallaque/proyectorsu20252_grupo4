<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $maintenances = Maintenance::select('id', 'name', 'start_date', 'end_date', 'status', 'created_at');

        if ($request->ajax()) {
            return DataTables::of($maintenances)
                ->addColumn("status_badge", function ($maintenance) {
                    if ($maintenance->status == 1) {
                        return '<span class="badge badge-success">Activo</span>';
                    } else {
                        return '<span class="badge badge-danger">Inactivo</span>';
                    }
                })
                ->addColumn("schedules", function ($maintenance) {
                    return '<button class="btn btn-light btn-sm btnSchedules" data-id="' . $maintenance->id . '" data-name="' . $maintenance->name . '">
                            <i class="far fa-calendar-alt"></i>
                        </button>';
                })
                ->addColumn("edit", function ($maintenance) {
                    return '<button class="btn btn-secondary btn-sm btnEdit" data-id="' . $maintenance->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($maintenance) {
                    return '<form action="' . route('admin.maintenances.destroy', $maintenance) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['status_badge', 'schedules', 'edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.maintenances.index', compact('maintenances'));
        }
    }

    public function create()
    {
        return view('admin.maintenances.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                "name" => "required|string|max:100",
                "start_date" => "required|date",
                "end_date" => "required|date|after_or_equal:start_date"
            ]);

            // Validar que no se solapen las fechas
            $overlap = Maintenance::where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })->exists();

            if ($overlap) {
                return response()->json([
                    "message" => "Las fechas se solapan con otro mantenimiento existente"
                ], 400);
            }

            Maintenance::create($request->all());

            return response()->json(["message" => "Mantenimiento registrado correctamente"], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al registrar mantenimiento",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $maintenance = Maintenance::find($id);
        return view('admin.maintenances.edit', compact('maintenance'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $maintenance = Maintenance::find($id);

            $request->validate([
                "name" => "required|string|max:100",
                "start_date" => "required|date",
                "end_date" => "required|date|after_or_equal:start_date"
            ]);

            // Validar que no se solapen las fechas (excluyendo el registro actual)
            $overlap = Maintenance::where('id', '!=', $id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                        ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->end_date);
                        });
                })->exists();

            if ($overlap) {
                return response()->json([
                    "message" => "Las fechas se solapan con otro mantenimiento existente"
                ], 400);
            }

            $maintenance->update($request->all());

            return response()->json(["message" => "Mantenimiento actualizado correctamente"], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al actualizar mantenimiento",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $maintenance = Maintenance::findOrFail($id);

            // Verificar si tiene horarios asociados
            if ($maintenance->schedules()->count() > 0) {
                return response()->json([
                    "message" => "No se puede eliminar este mantenimiento porque tiene horarios asociados"
                ], 400);
            }

            $maintenance->delete();
            return response()->json(["message" => "Mantenimiento eliminado correctamente"], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al eliminar mantenimiento",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
