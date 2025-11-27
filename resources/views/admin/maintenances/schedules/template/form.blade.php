<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('vehicle_id', 'Vehículo') !!}
            {!! Form::select('vehicle_id', $vehicles, null, [
                'class' => 'form-control',
                'placeholder' => 'Seleccione un vehículo',
                'required',
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('responsible_id', 'Responsable') !!}
            {!! Form::select('responsible_id', $employees, null, [
                'class' => 'form-control',
                'placeholder' => 'Seleccione un responsable',
                'required',
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('maintenance_type', 'Tipo de Mantenimiento') !!}
            {!! Form::select(
                'maintenance_type',
                [
                    'PREVENTIVO' => 'Preventivo',
                    'LIMPIEZA' => 'Limpieza',
                    'REPARACION' => 'Reparación',
                ],
                null,
                [
                    'class' => 'form-control',
                    'placeholder' => 'Seleccione un tipo',
                    'required',
                ],
            ) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('day_of_week', 'Día de la Semana') !!}
            {!! Form::select(
                'day_of_week',
                [
                    'LUNES' => 'Lunes',
                    'MARTES' => 'Martes',
                    'MIERCOLES' => 'Miércoles',
                    'JUEVES' => 'Jueves',
                    'VIERNES' => 'Viernes',
                    'SABADO' => 'Sábado',
                    'DOMINGO' => 'Domingo',
                ],
                null,
                [
                    'class' => 'form-control',
                    'placeholder' => 'Seleccione un día',
                    'required',
                ],
            ) !!}
        </div>

        <div class="form-group">
            {!! Form::label('start_time', 'Hora de Inicio') !!}
            {!! Form::time('start_time', null, ['class' => 'form-control', 'required']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('end_time', 'Hora de Fin') !!}
            {!! Form::time('end_time', null, ['class' => 'form-control', 'required']) !!}
            <small class="form-text text-muted">La hora de fin debe ser posterior a la hora de inicio</small>
        </div>

    </div>
</div>
