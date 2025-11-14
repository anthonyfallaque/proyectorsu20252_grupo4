<div class="row">
    <div class="col-8">
        <div class="form-group">
            {!! Form::label('name', 'Nombre') !!}
            {!! Form::text('name', null, ['class' => 'form-control','placeholder' => 'Ingrese el nombre','required','pattern' => '[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('description', 'Descripción') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control','placeholder' => 'Ingrese la descripción','required','rows' => 4 ]) !!}
        </div>
        <div class="form-group">
            {!! Form::file('logo', ['id' => 'logo','accept' => 'image/*','class' => 'form-control','hidden' => true]) !!}
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <div id="imageButton" style="cursor: pointer; width: 100%; text-align: center; padding: 10px;">
                <img style="cursor: pointer; width: 100%; height: 180px;" 
                src="{{ isset($brand) && $brand->logo != '' ? asset($brand->logo) : asset('storage/brand_logo/producto_var.webp') }}" 
                alt="Logo" width="50">
                <p>Haga click para seleccionar un imagen</p>
            </div>
        </div>
    </div>
</div>

<script>
    $('#logo').change(function() {
        var file = this.files[0];
        if(file){
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imageButton img').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    $('#imageButton').click(function() {
        $('#logo').click();
    });
</script>