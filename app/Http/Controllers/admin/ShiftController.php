<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $shifts = Shift::select(['id', 'name', 'description', 'hour_in', 'hour_out', 'created_at', 'updated_at']);

            return DataTables::of($shifts)
                ->addColumn('schedule', function ($shift) {
                    return '<span class="badge badge-primary">' . $shift->hour_in . ' - ' . $shift->hour_out . '</span>';
                })
                ->addColumn('description_badge', function ($shift) {
                    return $shift->description ?
                        '<span class="badge badge-info">' . Str::limit($shift->description, 30) . '</span>' :
                        '<span class="badge badge-secondary">Sin descripción</span>';
                })
                ->addColumn('action', function ($shift) {
                    $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $shift->id . '">
                                <i class="fas fa-edit"></i>
                            </button>';

                    $deleteBtn = '<form class="delete d-inline" action="' . route('admin.shifts.destroy', $shift->id) . '" method="POST">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>';

                    return $editBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['schedule', 'description_badge', 'action'])
                ->make(true);
        }

        return view('admin.shifts.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.shifts.create');
    }

    /**
     * Store a newly created resource in storage.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100|unique:shifts,name',
                'description' => 'nullable|string',
                'hour_in' => 'required|string',    // ✅ Agregar validación
                'hour_out' => 'required|string'    // ✅ Agregar validación
            ]);

            $shift = Shift::create([
                'name' => $request->name,
                'description' => $request->description,
                'hour_in' => $request->hour_in,      // ✅ Agregar
                'hour_out' => $request->hour_out     // ✅ Agregar
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Turno creado exitosamente.',
                'data' => $shift
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Shift $shift)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100|unique:shifts,name,' . $shift->id,
                'description' => 'nullable|string',
                'hour_in' => 'required|string',
                'hour_out' => 'required|string'
            ]);

            $shift->update([
                'name' => $request->name,
                'description' => $request->description,
                'hour_in' => $request->hour_in,
                'hour_out' => $request->hour_out
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Turno actualizado exitosamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        // Verificar si hay grupos de empleados asignados a este turno
        if ($shift->employeeGroups()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar este turno porque tiene grupos de empleados asignados.'
            ], 400);
        }

        $shift->delete();

        return response()->json([
            'message' => 'Turno eliminado exitosamente.'
        ], 200);
    }
}
