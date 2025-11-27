<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning">
            <strong>Información del Horario:</strong><br>
            Mantenimiento: {{ $schedule->maintenance->name }}<br>
            Día: {{ $schedule->day_of_week }}<br>
            Vehículo: {{ $schedule->vehicle->name }}<br>
            Fecha: {{ $activity->activity_date ?? 'Nueva' }}
        </div>

        <div class="form-group">
            {!! Form::label('observation', 'Observación') !!}
            {!! Form::textarea('observation', null, [
                'class' => 'form-control',
                'placeholder' => 'Ej: Todo conforme',
                'rows' => 4,
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('image', 'Imagen') !!}
            {!! Form::file('image', ['class' => 'form-control-file', 'accept' => 'image/*']) !!}
            <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>

            @if (isset($activity) && $activity->image)
                <div class="mt-2">
                    <strong>Imagen actual:</strong><br>
                    <img src="{{ asset('storage/' . $activity->image) }}" alt="Imagen actual" class="img-thumbnail"
                        style="max-width: 200px;">
                </div>
            @endif
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
                {!! Form::checkbox('completed', 1, isset($activity) ? $activity->completed : false, [
                    'class' => 'custom-control-input',
                    'id' => 'completed',
                ]) !!}
                {!! Form::label('completed', '¿Se realizó el mantenimiento?', ['class' => 'custom-control-label']) !!}
            </div>
        </div>
    </div>
</div>
