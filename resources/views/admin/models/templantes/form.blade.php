<div class="form-group">
    {!! Form::label('brand_id', 'Marca') !!}
    {!! Form::select('brand_id', $brands, null, ['class' => 'form-control','placeholder' => 'Seleccione una marca','required','pattern' => '[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+']) !!}
</div>

<div class="form-group">
    {!! Form::label('code', 'Código') !!}
    {!! Form::text('code', null, ['class' => 'form-control','placeholder' => 'Ingrese el código','required']) !!}
</div>

<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null, ['class' => 'form-control','placeholder' => 'Ingrese el nombre','required']) !!}
</div>

<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control','placeholder' => 'Ingrese la descripción','required','rows' => 4 ]) !!}
</div>
