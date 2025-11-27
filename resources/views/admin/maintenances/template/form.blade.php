<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del Mantenimiento') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ej: MANT. ENERO 2025', 'required']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('start_date', 'Fecha de Inicio') !!}
            {!! Form::date('start_date', null, ['class' => 'form-control', 'required']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('end_date', 'Fecha de Fin') !!}
            {!! Form::date('end_date', null, ['class' => 'form-control', 'required']) !!}
            <small class="form-text text-muted">La fecha de fin debe ser igual o posterior a la fecha de inicio</small>
        </div>

    </div>
</div>
