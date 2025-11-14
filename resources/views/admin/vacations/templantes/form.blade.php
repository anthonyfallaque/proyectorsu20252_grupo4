<?php

$isReadonly = isset($vacation) && in_array($vacation->status, ['Approved', 'Completed']);
?>

@if($isReadonly)
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Esta solicitud de vacaciones no puede ser editada porque está en estado "{{ $vacation->status == 'Approved' ? 'Aprobado' : 'Completado' }}".
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="employee_id">Empleado</label>
            <select class="form-control" id="employee_id" name="employee_id" required
                @if(isset($vacation)) disabled @elseif($isReadonly) disabled @endif>
                <option value="">Seleccione un empleado</option>
                @foreach($employees as $employee)
                <option value="{{ $employee->id }}"
                    {{ isset($vacation) && $vacation->employee_id == $employee->id ? 'selected' : '' }}
                    data-available-days="{{ $employee->available_days }}">
                    {{ $employee->name_with_last_name }} ({{ $employee->available_days }} días disponibles)
                </option>
                @endforeach
            </select>
            @if(isset($vacation))
            <input type="hidden" name="employee_id" value="{{ $vacation->employee_id }}">
            @elseif($isReadonly)
            <input type="hidden" name="employee_id" value="{{ $vacation->employee_id }}">
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="request_date">Fecha de Inicio</label>
            <?php
            $minDate = \Carbon\Carbon::now()->addDays(11)->format('Y-m-d');
            $defaultDate = isset($vacation) && $vacation->request_date > \Carbon\Carbon::now()->addDays(10)
                ? $vacation->request_date->format('Y-m-d')
                : $minDate;
            ?>
            <input type="date" class="form-control datepicker" id="request_date" name="request_date" required
                min="{{ $minDate }}"
                value="{{ $defaultDate }}"
                {{ $isReadonly ? 'readonly' : '' }}>
            <small class="text-muted">Las solicitudes deben hacerse con al menos 10 días de anticipación</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="requested_days">Días Solicitados</label>
            <input type="number" class="form-control" id="requested_days" name="requested_days" min="1" required
                value="{{ isset($vacation) ? $vacation->requested_days : '1' }}"
                {{ $isReadonly ? 'readonly' : '' }}>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="end_date">Fecha Final</label>
            <input type="date" class="form-control" id="end_date" name="end_date" readonly
                value="{{ isset($vacation) ? $vacation->end_date->format('Y-m-d') : '' }}">
            <small class="text-muted">Calculada automáticamente</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="available_days">Días Disponibles</label>
            <input type="number" class="form-control" id="available_days" name="available_days" readonly
                value="{{ isset($vacation) && isset($employees) ? $employees->where('id', $vacation->employee_id)->first()->available_days ?? '0' : '0' }}">
            <small class="text-muted">Basado en contrato del empleado</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Estado</label>
            <select class="form-control" id="status" name="status" required {{ $isReadonly ? 'disabled' : '' }}>
                @foreach($statusOptions as $option)
                <option value="{{ $option }}" {{ isset($vacation) && $vacation->status == $option ? 'selected' : '' }}>
                    @switch($option)
                    @case('Pending')
                    Pendiente
                    @break
                    @case('Approved')
                    Aprobado
                    @break
                    @case('Rejected')
                    Rechazado
                    @break
                    @case('Cancelled')
                    Cancelado
                    @break
                    @case('Completed')
                    Completado
                    @break
                    @default
                    {{ $option }}
                    @endswitch
                </option>
                @endforeach
            </select>
            @if($isReadonly)
            <input type="hidden" name="status" value="{{ $vacation->status }}">
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="notes">Notas</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" {{ $isReadonly ? 'readonly' : '' }}>{{ isset($vacation) ? $vacation->notes : '' }}</textarea>
        </div>
    </div>
</div>

<input type="hidden" name="original_requested_days" value="{{ isset($vacation) ? $vacation->requested_days : '0' }}">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if (!$('#employee_id').prop('disabled')) {
            $('#employee_id').select2({
                placeholder: 'Seleccione un empleado',
                dropdownParent: $('#modalVacation')
            });
        }
    });
</script>

<style>
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        padding: 6px 12px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background-color: #fff;
        font-size: 1rem;
        line-height: 1.5;
        box-sizing: border-box;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 2.25rem !important;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 2.25rem !important;
    }

    .select2-container {
        width: 100% !important;
    }
</style>