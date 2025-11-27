<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\Zone;
use App\Models\Scheduling;
use App\Models\Vacation;
use App\Models\Attendance;
use App\Models\Contract;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Estadísticas generales
        $totalVehicles = Vehicle::where('status', 1)->count();
        $totalEmployees = Employee::where('status', 1)->count();
        $totalZones = Zone::where('status', 'A')->count();
        $activeContracts = Contract::where('is_active', 1)->count();

        // Programaciones de hoy
        $todaySchedulings = Scheduling::with(['employeegroup.zone', 'shift', 'vehicle'])
            ->whereDate('date', $today)
            ->where('status', '!=', 0)
            ->get();

        // Asistencias de hoy
        $todayAttendances = Attendance::whereDate('attendance_date', $today)
            ->where('status', 1)
            ->count();

        // Vacaciones pendientes de aprobación
        $pendingVacations = Vacation::where('status', 'Pending')->count();

        // Empleados activos con contrato
        $employeesWithContract = Employee::whereHas('contracts', function($query) {
            $query->where('is_active', 1);
        })->count();

        // Zonas programadas hoy por turno
        $schedulingsByShift = Scheduling::with('shift', 'employeegroup.zone')
            ->whereDate('date', $today)
            ->where('status', '!=', 0)
            ->get()
            ->groupBy('shift_id');

        return view('dashboard', compact(
            'totalVehicles',
            'totalEmployees',
            'totalZones',
            'activeContracts',
            'todaySchedulings',
            'todayAttendances',
            'pendingVacations',
            'employeesWithContract',
            'schedulingsByShift',
            'today'
        ));
    }
}
