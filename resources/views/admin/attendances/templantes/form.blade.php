<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-12">
                {!! Form::label('employee_id', 'Empleado') !!} <span class="text-danger">*</span>
            </div>
            {!! Form::select('employee_id', $employees->pluck('full_name', 'id'), null, ['class' => 'form-control', 'placeholder' => 'Seleccione un empleado', 'required']) !!}
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('attendance_date', 'Fecha de Asistencia') !!} <span class="text-danger">*</span>
            {!! Form::date('attendance_date', now()->toDateString(), ['class' => 'form-control', 'required']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('status', 'Estado de Asistencia') !!} <span class="text-danger">*</span>
    {!! Form::select('status', [
        1 => 'Presente',
        0 => 'Ausente',
        2 => 'Justificado'
    ], 1, ['class' => 'form-control','disabled'=>true, 'required']) !!}
</div>

<div class="form-group">
    {!! Form::label('notes', 'Observaciones') !!}
    {!! Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => 'Notas u observaciones adicionales...', 'rows' => 3]) !!}
</div>

<script>
    $(document).ready(function() {
        $('#employee_id').select2({
            placeholder: 'Seleccione un empleado',  // Agrega un placeholder
            dropdownParent: $('#modalAttendance'),
            
        });

    });
</script>
