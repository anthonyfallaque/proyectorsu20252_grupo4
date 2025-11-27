@extends('adminlte::page')

@section('title', 'Dashboard - Proyecto RSU')

@section('content_header')
    <h1>Dashboard - Resumen General</h1>
@stop

@section('content')
    <div class="container-fluid">

        <!-- Fecha Actual -->
        <div class="mb-3 row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-calendar-day"></i>
                    <strong>Hoy:</strong> {{ \Carbon\Carbon::parse($today)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </div>
            </div>
        </div>

        <!-- Estadísticas Generales del Sistema -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalVehicles }}</h3>
                        <p>Vehículos Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <a href="{{ route('admin.vehicles.index') }}" class="small-box-footer">
                        Ver más <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $employeesWithContract }}</h3>
                        <p>Empleados con Contrato</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{{ route('admin.employees.index') }}" class="small-box-footer">
                        Ver más <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalZones }}</h3>
                        <p>Zonas Activas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <a href="{{ route('admin.zones.index') }}" class="small-box-footer">
                        Ver más <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $todayAttendances }}</h3>
                        <p>Asistencias Hoy</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="{{ route('admin.attendances.index') }}" class="small-box-footer">
                        Ver más <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas Importantes -->
        @if ($pendingVacations > 0)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Atención!</h5>
                        Hay <strong>{{ $pendingVacations }}</strong> solicitud(es) de vacaciones pendientes de aprobación.
                        <a href="{{ route('admin.vacations.index') }}" class="alert-link">Ver solicitudes</a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Programación de Hoy -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-check"></i>
                            Programación del Día
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.schedulings.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Ver Todas
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($schedulingsByShift->count() > 0)
                            @foreach ($schedulingsByShift as $shiftId => $schedulings)
                                @php
                                    $shift = $schedulings->first()->shift;
                                @endphp
                                <h5 class="mb-3">
                                    <span class="badge badge-primary">{{ $shift->name }}</span>
                                    <small class="text-muted">({{ $shift->hour_in }} - {{ $shift->hour_out }})</small>
                                </h5>

                                <div class="mb-4 row">
                                    @foreach ($schedulings as $scheduling)
                                        @php
                                            $zone = $scheduling->employeegroup->zone ?? null;
                                            $statusClass =
                                                $scheduling->status == 1
                                                    ? 'primary'
                                                    : ($scheduling->status == 2
                                                        ? 'success'
                                                        : 'warning');
                                            $statusText =
                                                $scheduling->status == 1
                                                    ? 'Programado'
                                                    : ($scheduling->status == 2
                                                        ? 'Completado'
                                                        : 'Reprogramado');
                                        @endphp
                                        <div class="mb-3 col-md-4">
                                            <div class="card card-outline card-{{ $statusClass }}">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $zone ? $zone->name : 'Sin zona' }}
                                                    </h6>
                                                    <p class="mb-1 card-text">
                                                        <small>
                                                            <i class="fas fa-truck"></i>
                                                            {{ $scheduling->vehicle->code ?? 'N/A' }}<br>
                                                            <i class="fas fa-users"></i>
                                                            {{ $scheduling->employeegroup->name ?? 'N/A' }}
                                                        </small>
                                                    </p>
                                                    <span
                                                        class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No hay programaciones para el día de hoy.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Accesos Rápidos
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-3 col-sm-6">
                                <a href="{{ route('admin.schedulings.create') }}" class="btn btn-app bg-primary">
                                    <i class="fas fa-calendar-plus"></i> Nueva Programación
                                </a>
                            </div>
                            <div class="mb-3 col-md-3 col-sm-6">
                                <a href="{{ route('admin.attendances.index') }}" class="btn btn-app bg-success">
                                    <i class="fas fa-user-check"></i> Registrar Asistencia
                                </a>
                            </div>
                            <div class="mb-3 col-md-3 col-sm-6">
                                <a href="{{ route('admin.vacations.index') }}" class="btn btn-app bg-warning">
                                    <i class="fas fa-plane"></i> Vacaciones
                                </a>
                            </div>
                            <div class="mb-3 col-md-3 col-sm-6">
                                <a href="{{ route('admin.changes.index') }}" class="btn btn-app bg-danger">
                                    <i class="fas fa-exchange-alt"></i> Cambios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('css')
    <style>
        .card-outline {
            border-top: 3px solid;
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Dashboard cargado correctamente');
    </script>
@stop
