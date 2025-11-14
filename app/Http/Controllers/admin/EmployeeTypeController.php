<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeTypeRequest;
use App\Models\EmployeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employeeTypes = EmployeeType::select(['id', 'name', 'description', 'created_at', 'updated_at']);

            return DataTables::of($employeeTypes)
                ->addColumn('action', function ($employeeType) {
                    $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $employeeType->id . '" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>';

                    $deleteBtn = '<form class="delete d-inline" action="' . route('admin.employee-types.destroy', $employeeType->id) . '" method="POST" style="margin-left: 5px;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';

                    return $editBtn . $deleteBtn;
                })
                ->editColumn('created_at', function ($employeeType) {
                    return $employeeType->created_at->format('d/m/Y');
                })
                ->editColumn('description', function ($employeeType) {
                    return $employeeType->description ?: '<span class="text-muted">Sin descripción</span>';
                })
                ->rawColumns(['action', 'description'])
                ->make(true);
        }

        return view('admin.employee-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.employee-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:employeetype,name|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'description' => 'nullable|string|min:10|max:500',
        ], [
            'name.required' => 'El nombre del tipo de empleado es obligatorio.',
            'name.max' => 'El nombre no puede exceder 100 caracteres.',
            'name.unique' => 'Ya existe un tipo de empleado con este nombre.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'description.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        try {
            DB::beginTransaction();

            $employeeType = EmployeeType::create([
                'name' => trim($request->name),
                'description' => $request->description ? trim($request->description) : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de empleado creado exitosamente.',
                'employee_type' => $employeeType
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tipo de empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeType $employeeType)
    {
        return view('admin.employee-types.edit', compact('employeeType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeType $employeeType)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('employeetype', 'name')->ignore($employeeType->id),
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/'
            ],
            'description' => 'nullable|string|min:10|max:500',
        ], [
            'name.required' => 'El nombre del tipo de empleado es obligatorio.',
            'name.max' => 'El nombre no puede exceder 100 caracteres.',
            'name.unique' => 'Ya existe un tipo de empleado con este nombre.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'description.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        try {
            DB::beginTransaction();

            $employeeType->update([
                'name' => trim($request->name),
                'description' => $request->description ? trim($request->description) : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de empleado actualizado exitosamente.',
                'employee_type' => $employeeType->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el tipo de empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Remove the specified resource from storage.
 */
public function destroy(EmployeeType $employeeType)
{
    try {
        DB::beginTransaction();

        $employeeType->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de empleado eliminado exitosamente.'
        ]);

    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollBack();
        
        // Verificar si es un error de constraint de foreign key (código 23000)
        if ($e->getCode() == 23000) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el tipo de empleado porque está asociado a uno o más empleados.'
            ], 400);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el tipo de empleado.'
        ], 500);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el tipo de empleado: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Verificar unicidad de nombre para validación dinámica
     */
    public function checkUnique(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $employeeTypeId = $request->input('employee_type_id');

        $query = EmployeeType::where($field, $value);
        
        if ($employeeTypeId) {
            $query->where('id', '!=', $employeeTypeId);
        }

        $exists = $query->exists();

        return response()->json(['unique' => !$exists]);
    }
}