@extends('adminlte::page')

@section('title', 'Programaciones')

@section('content')
<div class="p-2"></div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Nueva Programación</h3>
         <div class="card-tools">
            <a href="{{route('admin.schedulings.index')}}" class="btn btn-link"> Volver</a> 
            
 
        </div>
    </div>
    <div class="card-body">
        <label for="">Seleccione Turno</label>
        <div class="d-flex justify-content-center flex-wrap align-items-center" id="shift-button-container">
            @foreach ($shifts as $shift)
                <button type="button" class="btn btn-outline-primary shift-btn m-2" data-id="{{ $shift->id }}">
                    {{ $shift->name }}
                </button>
            @endforeach
        
            <button id="reset-shift" class="btn btn-link m-2 d-none">
                Restablecer
            </button>
        </div>
        

       <div id="contenidoDinamico">

       </div>
       
       {{-- Este input oculto guarda la selección final --}}
       <input type="hidden" name="selected_shift_id" id="selected_shift_id">
    </div>
</div>
@stop
@section('js')
<script>
    $(document).ready(function () {
        // Comprobar si hay una selección previa guardada
        const savedShiftId = localStorage.getItem('selectedShiftId');
        if (savedShiftId) {
            $('#selected_shift_id').val(savedShiftId);
            cargarContenidoDinamico(savedShiftId);
            $('.shift-btn').each(function () {
                const btn = $(this);
                if (btn.data('id') == savedShiftId) {
                    btn.removeClass('btn-outline-primary').addClass('btn-primary active');
                } else {
                    btn.hide();
                }
            });

            mostrarBotonRestablecer();
        }

        $('.shift-btn').on('click', function () {
            $('.shift-btn').removeClass('active btn-primary').addClass('btn-outline-primary').hide();
            $(this).removeClass('btn-outline-primary').addClass('btn-primary active').show();

            const selectedId = $(this).data('id');
            $('#selected_shift_id').val(selectedId);
            localStorage.setItem('selectedShiftId', selectedId);
            cargarContenidoDinamico(selectedId);

            mostrarBotonRestablecer();
        });

        // Botón restablecer (creado dinámicamente)
        function mostrarBotonRestablecer() {
            if ($('#btnResetShift').length === 0) {
                const btn = $('<button id="btnResetShift" class="btn btn-secondary m-2">Restablecer</button>');
                btn.on('click', function () {
                    localStorage.removeItem('selectedShiftId');
                    $('#selected_shift_id').val('');
                    $('#contenidoDinamico').html('');
                    $('.shift-btn').removeClass('active btn-primary').addClass('btn-outline-primary').show();
                    $(this).remove(); // Elimina el botón
                });
                $('.shift-btn').parent().append(btn);
            }
        }
    });

    function cargarContenidoDinamico(shiftId) {
        $.ajax({
            url: '{{ route('admin.schedulings.get-content', 'shift_id') }}'.replace('shift_id', shiftId),
            type: 'GET',
            success: function (response) {
                $('#contenidoDinamico').html(response);
            },
            error: function (xhr, status, error) {
                console.error('Error al cargar contenido dinámico:', error);
            }
        });
    }
</script>
@stop
