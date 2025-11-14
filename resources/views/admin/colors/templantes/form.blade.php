<div class="form-group">
    {!! Form::label('name', 'Nombre:') !!}
    {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del color','required' => true, 'maxlength' => 50, 'pattern' => '[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+']) !!}
    @error('name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    {!! Form::label('description', 'Descripción:') !!}
    {!! Form::textarea('description', old('description'), ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción del color','rows' => 3,'required' => true,'maxlength' => 255  ]) !!}
    @error('description')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
