<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employees = Employee::with('employeeType')
                ->select(['id', 'dni', 'names', 'lastnames', 'phone', 'status', 'type_id', 'photo', 'created_at', 'updated_at']);

            return DataTables::of($employees)
                ->addColumn('photo', function ($employee) {
                    if ($employee->photo) {
                        return '<img src="' . asset('storage/employees/' . $employee->photo) . '" alt="Foto" width="40" height="40" style="border-radius: 50%; object-fit: cover;">';
                    } else {
                        return '<div style="width: 40px; height: 40px; border-radius: 50%; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                    <i class="fas fa-user"></i>
                                </div>';
                    }
                })
                ->addColumn('full_name', function ($employee) {
                    return $employee->names . ' ' . $employee->lastnames;
                })
                ->addColumn('employee_type_name', function ($employee) {
                    return $employee->employeeType ? $employee->employeeType->name : 'Sin tipo';
                })
                ->addColumn('status_badge', function ($employee) {
                    return $employee->status ?
                        '<span class="badge badge-success">Activo</span>' :
                        '<span class="badge badge-danger">Inactivo</span>';
                })
                ->addColumn('action', function ($employee) {
                    $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $employee->id . '" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>';

                    $deleteBtn = '<form class="delete d-inline" action="' . route('admin.employees.destroy', $employee->id) . '" method="POST" style="margin-left: 5px;">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';

                    return $editBtn . $deleteBtn;
                })
                ->editColumn('created_at', function ($employee) {
                    return $employee->created_at->format('d/m/Y H:i');
                })
                ->editColumn('updated_at', function ($employee) {
                    return $employee->updated_at->format('d/m/Y H:i');
                })
                ->rawColumns(['photo', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.employees.index');
    }

    public function create()
    {
        $employeeTypes = EmployeeType::all();
        return view('admin.employees.create', compact('employeeTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|size:8|unique:employees,dni|regex:/^[0-9]+$/',
            'names' => 'required|string|max:100|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'lastnames' => 'required|string|max:200|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'email' => 'nullable|email|max:100|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:200|min:10',
            'type_id' => 'required|exists:employeetype,id',
            'birthday' => 'required|date|before:-18 years',
            'license' => 'nullable|string|max:20|unique:employees,license',
            'password' => 'required|string|min:8',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'dni.regex' => 'El DNI solo puede contener números.',
            'names.required' => 'Los nombres son obligatorios.',
            'names.regex' => 'Los nombres solo pueden contener letras y espacios.',
            'lastnames.required' => 'Los apellidos son obligatorios.',
            'lastnames.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'address.required' => 'La dirección es obligatoria.',
            'address.min' => 'La dirección debe tener al menos 10 caracteres.',
            'type_id.required' => 'Debe seleccionar un tipo de empleado.',
            'type_id.exists' => 'El tipo de empleado seleccionado no es válido.',
            'birthday.required' => 'La fecha de nacimiento es obligatoria.',
            'birthday.before' => 'Debe ser mayor de 18 años.',
            'license.unique' => 'Esta licencia ya está registrada.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La imagen debe ser JPG, PNG o JPEG.',
            'photo.max' => 'La imagen no puede ser mayor a 2MB.',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'dni' => trim($request->dni),
                'names' => trim($request->names),
                'lastnames' => trim($request->lastnames),
                'email' => $request->email ? trim($request->email) : null,
                'phone' => $request->phone ? trim($request->phone) : null,
                'address' => trim($request->address),
                'type_id' => $request->type_id,
                'birthday' => $request->birthday,
                'license' => $request->license ? trim($request->license) : null,
                'password' => Hash::make($request->password),
                'status' => $request->has('status') && $request->status == '1' ? 1 : 0,
            ];

            // Manejar la foto
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $photo->storeAs('public/employees', $photoName);
                $data['photo'] = $photoName;
            }

            $employee = Employee::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente.',
                'employee' => $employee
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Employee $employee)
    {
        $employeeTypes = EmployeeType::all();
        return view('admin.employees.edit', compact('employee', 'employeeTypes'));
    }

    public function update(Request $request, Employee $employee)
    {
        // Verificar si el tipo de empleado requiere licencia
        $employeeType = \App\Models\EmployeeType::find($request->type_id);
        $requiresLicense = $employeeType && stripos($employeeType->name, 'conductor') !== false;

        $rules = [
            'dni' => [
                'required',
                'string',
                'size:8',
                Rule::unique('employees', 'dni')->ignore($employee->id),
                'regex:/^[0-9]+$/'
            ],
            'names' => 'required|string|max:100|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'lastnames' => 'required|string|max:200|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('employees', 'email')->ignore($employee->id)
            ],
            'phone' => 'nullable|string|max:20|regex:/^(\+?51)?9[0-9]{8}$/',
            'address' => 'required|string|max:200|min:10',
            'type_id' => 'required|exists:employeetype,id',
            'birthday' => 'required|date|before:-18 years',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // Agregar validación de licencia solo si es conductor
        if ($requiresLicense) {
            $rules['license'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('employees', 'license')->ignore($employee->id)
            ];
        } else {
            $rules['license'] = [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'license')->ignore($employee->id)
            ];
        }

        $messages = [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'dni.regex' => 'El DNI solo puede contener números.',
            'names.required' => 'Los nombres son obligatorios.',
            'names.regex' => 'Los nombres solo pueden contener letras y espacios.',
            'lastnames.required' => 'Los apellidos son obligatorios.',
            'lastnames.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'address.required' => 'La dirección es obligatoria.',
            'address.min' => 'La dirección debe tener al menos 10 caracteres.',
            'type_id.required' => 'Debe seleccionar un tipo de empleado.',
            'type_id.exists' => 'El tipo de empleado seleccionado no es válido.',
            'birthday.required' => 'La fecha de nacimiento es obligatoria.',
            'birthday.before' => 'Debe ser mayor de 18 años.',
            'license.required' => 'La licencia es obligatoria para conductores.',
            'license.unique' => 'Esta licencia ya está registrada.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La imagen debe ser JPG, PNG o JPEG.',
            'photo.max' => 'La imagen no puede ser mayor a 2MB.',
        ];

        $request->validate($rules, $messages);

        try {
            DB::beginTransaction();
           
            $data = [
                'dni' => trim($request->dni),
                'names' => trim($request->names),
                'lastnames' => trim($request->lastnames),
                'email' => $request->email ? trim($request->email) : null,
                'phone' => $request->phone ? trim($request->phone) : null,
                'address' => trim($request->address),
                'type_id' => $request->type_id,
                'birthday' => $request->birthday,
                'license' => $request->license ? trim($request->license) : null,
                'status' => $request->has('status') && $request->status == '1' ? 1 : 0,
            ];

            // Manejar la foto
            if ($request->hasFile('photo')) {
                // Eliminar foto anterior si existe
                if ($employee->photo && \Storage::exists('public/employees/' . $employee->photo)) {
                    \Storage::delete('public/employees/' . $employee->photo);
                }

                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $photo->storeAs('public/employees', $photoName);
                $data['photo'] = $photoName;
            }

            if ($request->password) {
                $data['password'] = Hash::make($request->password);  // Usamos la clave 'password' para almacenar la contraseña hasheada
            }

            $employee->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente.',
                'employee' => $employee->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            DB::beginTransaction();

            // Eliminar la foto si existe
            if ($employee->photo && \Storage::exists('public/employees/' . $employee->photo)) {
                \Storage::delete('public/employees/' . $employee->photo);
            }

            $employee->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Verificar si es un error de constraint de foreign key (código 23000)
            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el empleado porque está asociado a uno o más contratos.'
                ], 400);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el empleado.'
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkUnique(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $employeeId = $request->input('employee_id');

        // Validar que el campo sea permitido
        if (!in_array($field, ['dni', 'email', 'license'])) {
            return response()->json(['unique' => false], 400);
        }

        $query = Employee::where($field, $value);
        
        if ($employeeId) {
            $query->where('id', '!=', $employeeId);
        }

        $exists = $query->exists();

        return response()->json(['unique' => !$exists]);
    }
}