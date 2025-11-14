@php
    $isEdit = isset($contract);
@endphp

<div class="form-group">
    {!! Form::label('employee_id', 'Empleado:') !!} <span class="text-danger">*</span>
    {!! Form::select('employee_id', $employees->pluck('name_with_last_name', 'id'), null, [
        'class' => 'form-control',
        'placeholder' => 'Seleccione un empleado',
        'required',
        'id' => 'employee_id',
        $isEdit ? 'disabled' : null // Deshabilita si es edición
    ]) !!}
    @if($isEdit)
        <input type="hidden" name="employee_id" value="{{ $contract->employee_id }}">
    @endif
    @error('employee_id')
    <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    {!! Form::label('contract_type', 'Tipo de Contrato') !!}
    {!! Form::select('contract_type', [
    'Nombrado' => 'Nombrado',
    'Contrato permanente' => 'Contrato permanente',
    'Temporal' => 'Temporal'
    ], null, ['class' => 'form-control', 'required', 'id' => 'contract_type']) !!}
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('start_date', 'Fecha de Inicio:') !!} <span class="text-danger">*</span>
            {!! Form::date('start_date', null, ['class' => 'form-control', 'required', 'id' => 'start_date']) !!}
            @error('start_date')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group" id="end_date_container">
            {!! Form::label('end_date', 'Fecha de Finalización:') !!}
            {!! Form::date('end_date', null, ['class' => 'form-control', 'id' => 'end_date']) !!}
            <small class="form-text text-muted">Dejar en blanco si es contrato indefinido</small>
            @error('end_date')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('salary', 'Salario:') !!} <span class="text-danger">*</span>
            {!! Form::number('salary', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'required']) !!}
            @error('salary')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            {!! Form::label('department_id', 'Departamento:') !!} <span class="text-danger">*</span>
            {!! Form::select('department_id', $departments, null, ['class' => 'form-control', 'placeholder' => 'Seleccione un departamento', 'required']) !!}
            @error('department_id')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div id="position_id_container" style="display:none;">
    {!! Form::hidden('position_id', null, ['id' => 'position_id_input']) !!}
</div>

<div class="row">
    <div class="col-md-6" id="vacation_days_container">
        <div class="form-group">
            {!! Form::label('vacation_days_per_year', 'Días de Vacaciones por Año:') !!} <span class="text-danger">*</span>
            {!! Form::number('vacation_days_per_year', null, ['class' => 'form-control', 'min' => '0', 'required']) !!}
            <div id="vacation_days_info" class="form-text text-muted"></div>
            @error('vacation_days_per_year')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('probation_period_months', 'Período de Prueba (meses):') !!} <span class="text-danger">*</span>
            {!! Form::number('probation_period_months', null, ['class' => 'form-control', 'min' => '0', 'required']) !!}
            @error('probation_period_months')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="custom-control custom-switch">
                {!! Form::checkbox('is_active', 1, null, ['class' => 'custom-control-input', 'id' => 'is_active']) !!}
                {!! Form::label('is_active', '¿Contrato Activo?', ['class' => 'custom-control-label']) !!}
            </div>
            @error('is_active')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6" id="termination_reason_container">
        <div class="form-group">
            {!! Form::label('termination_reason', 'Motivo de Terminación:') !!}
            {!! Form::textarea('termination_reason', null, ['class' => 'form-control', 'rows' => 2]) !!}
            <small class="form-text text-muted">Solo aplica si el contrato no está activo</small>
            @error('termination_reason')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if (!$('#employee_id').prop('disabled')) {
            $('#employee_id').select2({
                placeholder: 'Seleccione un empleado',
                dropdownParent: $('#modalContract')
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