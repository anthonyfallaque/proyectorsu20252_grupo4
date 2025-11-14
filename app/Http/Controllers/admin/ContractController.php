<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Department;
use Yajra\DataTables\Facades\DataTables;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::with(['employee', 'position', 'department'])
            ->select(
                'id',
                'employee_id',
                'contract_type',
                'start_date',
                'end_date',
                'salary',
                'position_id',
                'department_id',
                'is_active'
            )->get();

        if ($request->ajax()) {
            return DataTables::of($contracts)
                ->addColumn('employee_name', function ($contract) {
                    return $contract->employee->names . ' ' . $contract->employee->lastnames ?? 'N/A';
                })
                ->addColumn('position', function ($contract) {
                    return $contract->position->name ?? 'N/A';
                })
                ->addColumn('department', function ($contract) {
                    return $contract->department->name ?? 'N/A';
                })
                ->addColumn('status', function ($contract) {
                    return $contract->is_active ?
                        '<span class="badge bg-success">Activo</span>' :
                        '<span class="badge bg-danger">Inactivo</span>';
                })
                ->addColumn('action', function ($contract) {
                    return "
                    <button class='btn btn-warning btn-sm btnEditar' id='" . $contract->id . "'><i class='fas fa-edit'></i></button>
                    <form action=" . route('admin.contracts.destroy', $contract->id) . " id='delete-form-" . $contract->id . "' method='POST' class='d-inline'>
                        " . csrf_field() . "
                        " . method_field('DELETE') . "
                        <button type='button' onclick='confirmDelete(" . $contract->id . ")' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></button>
                    </form>
                    ";
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        } else {
            return view('admin.contracts.index', compact('contracts'));
        }
    }

    public function create()
    {
        $employees = Employee::select('id', 'names', 'lastnames')
            ->whereDoesntHave('contracts', function ($query) {
                $query->where('is_active', 1);
            })
            ->where(function ($query) {
                $query->whereDoesntHave('contracts', function ($subQuery) {
                    $subQuery->where('contract_type', 'Temporal')
                        ->where('is_active', 0)
                        ->whereNotNull('end_date')
                        ->where('end_date', '>', now()->subMonths(4));
                });
            })
            ->get()
            ->map(function ($employee) {
                $employee->name_with_last_name = $employee->names . ' ' . $employee->lastnames;
                return $employee;
            });

        $positions = EmployeeType::pluck('name', 'id');
        $departments = Department::pluck('name', 'id');

        return view('admin.contracts.create', compact('employees', 'positions', 'departments'));
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'employee_id' => 'required|exists:employees,id',
                'contract_type' => 'required|string|max:100',
                'start_date' => 'required|date',
                'salary' => 'required|numeric|min:0',
                'department_id' => 'required|exists:departments,id',
                'vacation_days_per_year' => 'sometimes|integer|min:0',
                'probation_period_months' => 'sometimes|integer|min:0',
            ];

            if (!in_array($request->contract_type, ['Nombrado', 'Contrato permanente'])) {
                $rules['end_date'] = 'required|date|after_or_equal:start_date';
            }

            $request->validate($rules);

            $lastTemporalContract = Contract::where('employee_id', $request->employee_id)
                ->where('contract_type', 'Temporal')
                ->where('is_active', 0)
                ->whereNotNull('end_date')
                ->orderBy('end_date', 'desc')
                ->first();

            if ($lastTemporalContract) {
                $endDate = \Carbon\Carbon::parse($lastTemporalContract->end_date);
                $fourMonthsLater = $endDate->addMonths(4);

                if (now() < $fourMonthsLater) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este empleado tuvo un contrato temporal que terminó el ' .
                            $lastTemporalContract->end_date->format('d/m/Y') .
                            '. Debe esperar hasta el ' .
                            $fourMonthsLater->format('d/m/Y') .
                            ' para poder tener un nuevo contrato (período de enfriamiento de 4 meses).'
                    ], 422);
                }
            }

            $data = $request->all();
            if (empty($data['position_id'])) {
                try {
                    $employee = Employee::findOrFail($data['employee_id']);
                    $data['position_id'] = $employee->type_id ?? 1;
                } catch (\Exception $e) {
                    $data['position_id'] = 1;
                }
            }

            if (in_array($request->contract_type, ['Nombrado', 'Contrato permanente'])) {
                $data['end_date'] = null;
                $data['vacation_days_per_year'] = 30;
            } else if ($request->contract_type === 'Temporal') {
                $data['vacation_days_per_year'] = 0;
            } else {
                $data['vacation_days_per_year'] = $request->filled('vacation_days_per_year') ? $request->vacation_days_per_year : 15;
            }

            $data['probation_period_months'] = $request->filled('probation_period_months') ? $request->probation_period_months : 3;
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            Contract::create($data);
            return response()->json(['success' => true, 'message' => 'Contrato creado exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al crear el contrato: ' . $th->getMessage()]);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $rules = [
                'employee_id' => 'required|exists:employees,id',
                'contract_type' => 'required|string|max:100',
                'start_date' => 'required|date',
                'salary' => 'required|numeric|min:0',
                'department_id' => 'required|exists:departments,id',
                'vacation_days_per_year' => 'sometimes|integer|min:0',
                'probation_period_months' => 'sometimes|integer|min:0',
                'termination_reason' => 'nullable|string',
            ];

            if (in_array($request->contract_type, ['Nombrado', 'Contrato permanente'])) {
                $rules['end_date'] = 'nullable|date|after_or_equal:start_date';
            } else {
                $rules['end_date'] = 'required|date|after_or_equal:start_date';
            }

            $request->validate($rules);

            $data = $request->all();

            if (empty($data['position_id'])) {
                try {
                    $employee = Employee::findOrFail($data['employee_id']);

                    $data['position_id'] = $employee->type_id ?? 1;
                } catch (\Exception $e) {
                    $data['position_id'] = 1;
                }
            }

            if (in_array($request->contract_type, ['Nombrado', 'Contrato permanente'])) {
                $data['end_date'] = null;
                $data['vacation_days_per_year'] = 30;
            } else if ($request->contract_type === 'Temporal') {
                $data['vacation_days_per_year'] = 0;
            } else {
                $data['vacation_days_per_year'] = $request->filled('vacation_days_per_year') ? $request->vacation_days_per_year : 15;
            }

            $data['probation_period_months'] = $request->filled('probation_period_months') ? $request->probation_period_months : 3;
            $data['is_active'] = $request->has('is_active') && $request->is_active == 1 ? 1 : 0;

            if ($data['is_active'] == 1) {
                $data['termination_reason'] = null;
            }


            $contract = Contract::findOrFail($id);
            $contract->update($data);

            return response()->json(['success' => true, 'message' => 'Contrato actualizado exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el contrato: ' . $th->getMessage()]);
        }
    }

    public function edit(string $id)
    {
        $contract = Contract::findOrFail($id);

        $employees = Employee::where('id', $contract->employee_id)
            ->get()
            ->map(function ($employee) {
                $employee->name_with_last_name = $employee->names . ' ' . $employee->lastnames;
                return $employee;
            });

        $positions = EmployeeType::pluck('name', 'id');
        $departments = Department::pluck('name', 'id');

        return view('admin.contracts.edit', compact('contract', 'employees', 'positions', 'departments'));
    }


    public function destroy(string $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $hasVacations = \App\Models\Vacation::where('employee_id', $contract->employee_id)->exists();
            if ($hasVacations) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el contrato porque el empleado tiene vacaciones registradas.'
                ], 422);
            }
            $hasSchedulings = \App\Models\Scheduling::whereHas('groupdetail', function ($q) use ($contract) {
                $q->where('employee_id', $contract->employee_id);
            })->exists();

            if ($hasSchedulings) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el contrato porque el empleado tiene programaciones registradas.'
                ], 422);
            }

            $contract->delete();
            return response()->json(['success' => true, 'message' => 'Contrato eliminado exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar el contrato: ' . $th->getMessage()]);
        }
    }
}
