@extends('adminlte::page')

@section('title', 'Cambios')


@section('css')
<style>
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px) !important; 
        padding: 6px 12px;
    }

    .select2-container--default .select2-dropdown {
        background-color: #f8f9fa !important;  
        border-radius: 4px;
    }

    .select2-container--default .select2-selection__rendered {
        color: #333 !important; 
    }

</style>
@stop

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade " id="modalChange" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalLongTitle"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          
        </div>
      </div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exchange-alt"></i> Cambios de Programaciones
        </h3>

        <div class="card-tools">
            <button id="btnNewBrand" class="btn btn-primary" ><i class="fas fa-plus"></i> Agregar Cambio</button>    
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Fecha de inicio: <span class="text-danger">*</span></label>
                <input type="date" value="{{$fechaActual}}" name="start_date" id="start_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">Fecha de fin: </label>
                <input type="date" name="end_date" id="end_date" class="form-control">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-info" id="btnFilter">Filtrar</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" id="datatable" style="width:100%">
                <thead >
                    <tr>
                        <th>FECHA CAMBIO</th>
                        <th>PROGRAMACIÓN</th>
                        <th>GRUPO</th>
                        <th>TIPO</th>
                        <th>VALOR ANTERIOR</th>
                        <th>VALOR NUEVO</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>
    
</div>
@stop

@section('css')

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
   
   <script>
  $(document).ready(function() {
        var table = $('#datatable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.changes.index') }}",
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                type: "GET"
            },
            columns: [
                { data: 'change_date' },
                { data: 'scheduled_date' },
                { data: 'group_employees' },
                { data: 'type' },
                { data: 'old_value' },
                { data: 'new_value' },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });
    });

   $('#btnNewBrand').click(function() {
    $.ajax({
        url: "{{ route('admin.changes.create') }}",
        type: 'GET',
        success: function(response) {
            $('#ModalLongTitle').text('Agregar Nuevo Cambio');
            $('#modalChange .modal-body').html(response);
            $('#modalChange').modal('show');

            $('#modalChange form').submit(function(e) {
                e.preventDefault(); 

                console.log('Formulario enviado'); 
                var form = $(this);
                const changeTypeInput = document.getElementById('change_type');

                if(changeTypeInput.value === ''){
                    changeTypeInput.style.borderColor = 'red';
                    return
                }

                Swal.fire({
                    title: '¿Confirmar cambios?',
                    text: "Se aplicarán los cambios registrados.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                            title: 'Cargando...',
                            text: 'Por favor espera...',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading(); // Muestra el loading spinner
                            }
                        });
                        var startDate = $('#start_date').val(); // Obtener valor del input Fecha de Inicio
                        var endDate = $('#end_date').val(); // Obtener valor del input Fecha de Fin
                        var changeType = $('#change_type').val(); // Obtener valor del select Tipo de Cambio
                        var select_group_turno = $('#select_group_turno').val();
                        var selectShift = $('#selectShift').val();
                        var selectGroupVehicle = $('#selectGroupVehicle').val();
                        var selectVehicle = $('#selectVehicle').val();
                        var selectCurrentEmployee = $('#selectCurrentEmployee').val();
                        var selectNewEmployee = $('#selectNewEmployee').val();
                       
                        if(changeType=='personal'){
                            var reason_id = 1 
                        }else if(changeType=='turno'){
                            var reason_id = 2
                        }else{
                            var reason_id = 3
                        }
                        
                        const data = {
                            startDate:startDate,
                            endDate:endDate,
                            group_turno:select_group_turno,
                            new_shift_id:selectShift,
                            groupvehicle:selectGroupVehicle,
                            new_vehicle_id:selectVehicle,
                            old_employee:selectCurrentEmployee,
                            new_employee:selectNewEmployee,
                            reason_id:reason_id,
                        }
                        
                        

                        console.log('Data:', data);
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: data,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $('#modalChange').modal('hide');
                                Swal.close();
                                console.log(response);

                                table.ajax.reload(null, false);

                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                                
                            },
                            error: function( xhr) {
                                Swal.close(); 
                                var response = xhr.responseJSON;
                                Swal.fire({
                                    icon: 'error',
                                    title: '¡Error!',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        })



                        
                    }
                });
            });
        },
        error: function(xhr, status, error) {
            console.log('Error al cargar formulario', error);
        }
    });
});

    $(document).on('click', '.btnEditar', function() {
        var brandId = $(this).attr('id');
        $.ajax({
            url: "{{ route('admin.changes.edit', 'id') }}".replace('id', brandId),
            type: 'GET',
            success: function(response) {
                $('#ModalLongTitle').text('Editar Cambio');
                $('#modalBrand .modal-body').html(response);
                $('#modalBrand').modal('show');

                $('#modalBrand form').submit(function(e){
                    e.preventDefault();
                    var form = $(this);
                    var formData = new FormData(this);

                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            
                            $('#modalBrand').modal('hide');
                            
                            table.ajax.reload(null, false);

                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                   
                            
                        },
                       error: function( xhr) {
                            var response = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    })
                })
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });

  

    $(document).on('submit','.delete',function(e){
        e.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Este cambio no se puede deshacer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                var form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    error: function(xhr, status, error) {
                    var response = xhr.responseJSON;
                    Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            }
        });
    });
       

    $('#btnFilter').on('click', function() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (startDate === '' || startDate === null) {
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: 'Por favor, selecciona al menos la fecha de inicio para filtrar.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        console.log('Fecha de inicio:', startDate);
        console.log('Fecha de fin:', endDate);


        if (endDate !== '' ) {
            
            if (startDate > endDate) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'La fecha de fin no puede ser menor que la fecha de inicio.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            // Recargar el DataTable con las fechas
            refreshTable();
            return;
        }

        refreshTable();
       
    });

</script>

@stop