<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vacation;
use App\Models\Employee;
use App\Models\Contract;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class VacationController extends Controller
{


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $employeesWithEligibleContract = Contract::where('is_active', true)
                ->whereIn('contract_type', ['Nombrado', 'Contrato permanente'])
                ->pluck('employee_id')
                ->toArray();

            $vacationsQuery = Vacation::with(['employee'])
                ->whereIn('employee_id', $employeesWithEligibleContract);

            if ($request->filled('start_date')) {
                $vacationsQuery->whereDate('request_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $vacationsQuery->whereDate('request_date', '<=', $request->end_date);
            }

            $vacations = $vacationsQuery
                ->select(
                    'id',
                    'employee_id',
                    'request_date',
                    'requested_days',
                    'end_date',
                    'status',
                    'notes'
                )->get()
                ->map(function ($vacation) {
                    $contract = Contract::where('employee_id', $vacation->employee_id)
                        ->where('is_active', true)
                        ->first();

                    $vacation->current_available_days = $contract ? $contract->vacation_days_per_year : 0;

                    return $vacation;
                });

            return DataTables::of($vacations)
                ->addColumn('employee_name', function ($vacation) {
                    return $vacation->employee->names . ' ' . $vacation->employee->lastnames ?? 'N/A';
                })
                ->addColumn('request_date_formatted', function ($vacation) {
                    return Carbon::parse($vacation->request_date)->format('d/m/Y');
                })
                ->addColumn('current_available_days', function ($vacation) {
                    return $vacation->current_available_days;
                })
                ->addColumn('end_date_formatted', function ($vacation) {
                    return Carbon::parse($vacation->end_date)->format('d/m/Y');
                })
                ->addColumn('status_badge', function ($vacation) {
                    $badgeClass = '';
                    switch ($vacation->status) {
                        case 'Approved':
                            $badgeClass = 'bg-success';
                            $statusText = 'Aprobado';
                            break;
                        case 'Pending':
                            $badgeClass = 'bg-warning';
                            $statusText = 'Pendiente';
                            break;
                        case 'Rejected':
                            $badgeClass = 'bg-danger';
                            $statusText = 'Rechazado';
                            break;
                        case 'Cancelled':
                            $badgeClass = 'bg-secondary';
                            $statusText = 'Cancelado';
                            break;
                        case 'Completed':
                            $badgeClass = 'bg-info';
                            $statusText = 'Completado';
                            break;
                        default:
                            $badgeClass = 'bg-primary';
                            $statusText = $vacation->status;
                    }
                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($vacation) {
                    return '
                    <button class="btn btn-warning btn-sm btnEditar" id="' . $vacation->id . '">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="' . route('admin.vacations.destroy', $vacation->id) . '" 
                          id="delete-form-' . $vacation->id . '" 
                          method="POST" 
                          class="d-inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" 
                                onclick="confirmDelete(' . $vacation->id . ')" 
                                class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                ';
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        } else {
            return view('admin.vacations.index');
        }
    }

    public function create()
    {
        $employeesWithEligibleContract = Contract::where('is_active', true)
            ->whereIn('contract_type', ['Nombrado', 'Contrato permanente'])
            ->pluck('employee_id')
            ->toArray();

        $employeesWithPendingVacations = Vacation::where('status', 'Pending')
            ->pluck('employee_id')
            ->toArray();

        $employees = Employee::select('id', 'names as name', 'lastnames as last_name')
            ->whereIn('id', $employeesWithEligibleContract)
            ->whereNotIn('id', $employeesWithPendingVacations)
            ->where('status', true)
            ->get()
            ->map(function ($employee) {
                $employee->name_with_last_name = $employee->name . ' ' . $employee->last_name;

                $contract = Contract::where('employee_id', $employee->id)
                    ->where('is_active', true)
                    ->first();

                $employee->available_days = $contract ? $contract->vacation_days_per_year : 0;

                return $employee;
            });

        $statusOptions = ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Completed'];

        return view('admin.vacations.create', compact('employees', 'statusOptions'));
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'employee_id' => 'required|exists:employees,id',
                'request_date' => 'required|date|after:' . Carbon::now()->addDays(10)->format('Y-m-d'),
                'requested_days' => 'required|integer|min:1',
                'status' => 'required|string|in:Pending,Approved,Rejected,Cancelled,Completed',
                'notes' => 'nullable|string|max:255',
            ];

            $request->validate($rules);

            $existingPendingVacation = Vacation::where('employee_id', $request->employee_id)
                ->where('status', 'Pending')
                ->first();

            if ($existingPendingVacation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este empleado ya tiene una solicitud de vacaciones pendiente. No puede crear otra hasta que la actual sea procesada.'
                ], 422);
            }

            $contract = Contract::where('employee_id', $request->employee_id)
                ->where('is_active', true)
                ->first();

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado no tiene un contrato activo'
                ], 400);
            }

            $availableDays = $contract->vacation_days_per_year;

            if ($request->requested_days > $availableDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado no tiene suficientes días de vacaciones disponibles'
                ], 400);
            }

            $vacation = new Vacation();
            $vacation->employee_id = $request->employee_id;
            $vacation->request_date = $request->request_date;
            $vacation->requested_days = $request->requested_days;
            $vacation->end_date = Carbon::parse($request->request_date)->addDays($request->requested_days);
            $vacation->status = $request->status;
            $vacation->notes = $request->notes;
            $vacation->save();

            if ($request->status === 'Approved') {
                $contract->vacation_days_per_year = $availableDays - $request->requested_days;
                $contract->save();
            }

            return response()->json(['success' => true, 'message' => 'Solicitud de vacaciones creada exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error al crear la solicitud de vacaciones: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $vacation = Vacation::findOrFail($id);

        $employeesWithEligibleContract = Contract::where('is_active', true)
            ->whereIn('contract_type', ['Nombrado', 'Contrato permanente'])
            ->pluck('employee_id')
            ->toArray();

        $employeesWithPendingVacations = Vacation::where('status', 'Pending')
            ->where('id', '!=', $id)
            ->pluck('employee_id')
            ->toArray();

        $employees = Employee::select('id', 'names as name', 'lastnames as last_name')
            ->whereIn('id', $employeesWithEligibleContract)
            ->where(function ($query) use ($employeesWithPendingVacations, $vacation) {
                $query->whereNotIn('id', $employeesWithPendingVacations)
                    ->orWhere('id', $vacation->employee_id);
            })
            ->where('status', true)
            ->get()
            ->map(function ($employee) {
                $employee->name_with_last_name = $employee->name . ' ' . $employee->last_name;

                $contract = Contract::where('employee_id', $employee->id)
                    ->where('is_active', true)
                    ->first();

                $employee->available_days = $contract ? $contract->vacation_days_per_year : 0;

                return $employee;
            });

        $statusOptions = ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Completed'];

        return view('admin.vacations.edit', compact('vacation', 'employees', 'statusOptions'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            $oldStatus = $vacation->status;
            $oldRequestedDays = $vacation->requested_days;
            $oldEmployeeId = $vacation->employee_id;

            if ($request->employee_id != $oldEmployeeId) {
                $existingPendingVacation = Vacation::where('employee_id', $request->employee_id)
                    ->where('status', 'Pending')
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingPendingVacation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El empleado seleccionado ya tiene una solicitud de vacaciones pendiente.'
                    ], 422);
                }
            }

            if ($vacation->status != 'Pending') {
                $rules = [
                    'employee_id' => 'required|exists:employees,id',
                    'request_date' => 'required|date',
                    'requested_days' => 'required|integer|min:1',
                    'status' => 'required|string|in:Pending,Approved,Rejected,Cancelled,Completed',
                    'notes' => 'nullable|string|max:255',
                ];
            } else {
                $rules = [
                    'employee_id' => 'required|exists:employees,id',
                    'request_date' => 'required|date|after:' . Carbon::now()->addDays(10)->format('Y-m-d'),
                    'requested_days' => 'required|integer|min:1',
                    'status' => 'required|string|in:Pending,Approved,Rejected,Cancelled,Completed',
                    'notes' => 'nullable|string|max:255',
                ];
            }

            $request->validate($rules);

            $contract = Contract::where('employee_id', $request->employee_id)
                ->where('is_active', true)
                ->first();

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado no tiene un contrato activo'
                ], 400);
            }

            $currentAvailableDays = $contract->vacation_days_per_year;

            if ($oldStatus === 'Approved' && $oldEmployeeId == $request->employee_id) {
                $currentAvailableDays += $oldRequestedDays;
            }

            if ($request->status === 'Approved' && $request->requested_days > $currentAvailableDays) {
                return response()->json([
                    'success' => false,
                    'message' => "El empleado solo tiene {$currentAvailableDays} días disponibles. No puede solicitar {$request->requested_days} días."
                ], 400);
            }

            $vacation->employee_id = $request->employee_id;
            $vacation->request_date = $request->request_date;
            $vacation->requested_days = $request->requested_days;
            $vacation->end_date = Carbon::parse($request->request_date)->addDays($request->requested_days);
            $vacation->status = $request->status;
            $vacation->notes = $request->notes;
            $vacation->save();

            if ($request->status === 'Approved') {
                $contract->vacation_days_per_year = $currentAvailableDays - $request->requested_days;
                $contract->save();
            } else if ($oldStatus === 'Approved' && $request->status !== 'Approved') {
                $contract->vacation_days_per_year = $currentAvailableDays;
                $contract->save();
            }

            return response()->json(['success' => true, 'message' => 'Solicitud de vacaciones actualizada exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la solicitud de vacaciones: ' . $th->getMessage()], 500);
        }
    }
    public function destroy(string $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);

            if ($vacation->status === 'Approved') {
                $contract = Contract::where('employee_id', $vacation->employee_id)
                    ->where('is_active', true)
                    ->first();

                if ($contract) {
                    $contract->vacation_days_per_year += $vacation->requested_days;
                    $contract->save();
                }
            }

            $vacation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de vacaciones eliminada exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud de vacaciones: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getEmployeeAvailableDays($employeeId)
    {
        try {
            $contract = Contract::where('employee_id', $employeeId)
                ->where('is_active', true)
                ->first();

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado no tiene un contrato activo'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'available_days' => $contract->vacation_days_per_year
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener días disponibles: ' . $th->getMessage()
            ], 500);
        }
    }

    public function calculateDays(Request $request)
    {
        try {
            $requestDate = $request->request_date;
            $requestedDays = $request->requested_days;

            if (!$requestDate || !$requestedDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos incompletos para el cálculo'
                ], 400);
            }

            $endDate = Carbon::parse($requestDate)->addDays($requestedDays)->format('Y-m-d');

            return response()->json([
                'success' => true,
                'end_date' => $endDate
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular fecha: ' . $th->getMessage()
            ], 500);
        }
    }

    public function changeStatus(Request $request, Vacation $vacation)
    {
        try {
            $oldStatus = $vacation->status;
            $newStatus = $request->status;

            if (!in_array($newStatus, ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado no válido'
                ], 400);
            }

            $contract = Contract::where('employee_id', $vacation->employee_id)
                ->where('is_active', true)
                ->first();

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado no tiene un contrato activo'
                ], 400);
            }

            if ($oldStatus !== 'Approved' && $newStatus === 'Approved') {
                if ($vacation->requested_days > $contract->vacation_days_per_year) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El empleado no tiene suficientes días de vacaciones disponibles'
                    ], 400);
                }

                $contract->vacation_days_per_year -= $vacation->requested_days;
                $contract->save();
            } else if ($oldStatus === 'Approved' && $newStatus !== 'Approved') {
                $contract->vacation_days_per_year += $vacation->requested_days;
                $contract->save();
            }

            $vacation->status = $newStatus;
            $vacation->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado de vacaciones actualizado exitosamente'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $th->getMessage()
            ], 500);
        }
    }

    public function checkAvailableDays(Request $request)
    {
        try {
            $employeeId = $request->employee_id;
            $isEdit = $request->is_edit == "true";
            $vacationId = $request->vacation_id;

            if (!$employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de empleado no proporcionado'
                ], 400);
            }

            $contract = Contract::where('employee_id', $employeeId)
                ->where('is_active', true)
                ->first();

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado no tiene un contrato activo'
                ], 400);
            }

            $availableDays = $contract->vacation_days_per_year;

            if ($isEdit && $vacationId) {
                $vacation = Vacation::find($vacationId);
                if ($vacation && $vacation->employee_id == $employeeId && $vacation->status === 'Approved') {
                    $availableDays += $vacation->requested_days;
                }
            }

            return response()->json([
                'success' => true,
                'available_days' => $availableDays
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener días disponibles: ' . $th->getMessage()
            ], 500);
        }
    }
}
