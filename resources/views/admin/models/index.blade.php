@extends('adminlte::page')

@section('title', 'Modelos')

@section('content_header')
    
@stop

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade" id="modalModel" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
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
        <h3 class="card-title">Lista de Modelos</h3>
        <div class="card-tools">
            <button id="btnNewModel" class="btn btn-primary" ><i class="fas fa-plus"></i> Agregar Nuevo Modelo</button> 
        </div>
    </div>
    <div class="card-body table-responsive">
            <table class="table table-striped" id="datatable" style="width:100%">
                <thead >
                    <tr>
                        <th>MODELO</th>
                        <th>MARCA</th>
                        <th>CODIGO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>CREADO</th>
                        <th>ACTUALIZADO</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($models as $model)
                        <tr>
                            <td>{{ $model->model_name }}</td>
                            <td>{{ $model->brand_name }}</td>
                            <td>{{ $model->code }}</td>  
                            <td>{{ $model->description }}</td>  
                            <td>{{ $model->created_at }}</td>
                            <td>{{ $model->updated_at }}</td>
                            <td>
                                <button type="button" class="btn btn-warning btnEditar" id="{{ $model->id }}"> <i class="fas fa-edit"></i></button>
                                <form action="{{ route('admin.models.destroy', $model->id) }}" id="delete-form-{{ $model->id }}" method="POST" class="d-inline formDelete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"> <i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                   
                    @endforeach
                </tbody>
            </table>
        
       
    
</div>
@stop

@section('css')

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

   <script>

    $('#btnNewModel').click(function() {
        $.ajax({
            url: "{{ route('admin.models.create') }}",
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Nuevo Modelo');
                $('#modalModel .modal-body').html(response);
                $('#modalModel').modal('show');

                $('#modalModel form').submit(function(e){
                    e.preventDefault();
                    var form = $(this)
                    var formData = new FormData(this);
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                        
                            $('#modalModel').modal('hide');
                            refreshTable();
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
            }
        });
    });

    $(document).on('click', '.btnEditar', function() {
        var modelId = $(this).attr('id');
        $.ajax({
            url: "{{ route('admin.models.edit', 'id') }}".replace('id', modelId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Editar Modelo');
                $('#modalModel .modal-body').html(response);
                $('#modalModel').modal('show');

                $('#modalModel form').submit(function(e){
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
                           
                            $('#modalModel').modal('hide');
                            refreshTable();
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
            }
        });
    });
    
    $(document).ready(function() {
    $('#datatable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        ajax: "{{ route('admin.models.index') }}",
        processing: true,
        serverSide: true,
        columns: [
            { data: 'model_name' },
            { data: 'brand_name' },
            { data: 'description' },
            { data: 'code' },
            {
                data: 'created_at',
                render: function(data) {
                    if (!data) return '';
                    let fecha = new Date(data);
                    let dia = ('0' + fecha.getDate()).slice(-2);
                    let mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
                    let anio = fecha.getFullYear();
                    return `${dia}-${mes}-${anio}`;
                }
            },
            {
                data: 'updated_at',
                render: function(data) {
                    if (!data) return '';
                    let fecha = new Date(data);
                    let dia = ('0' + fecha.getDate()).slice(-2);
                    let mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
                    let anio = fecha.getFullYear();
                    return `${dia}-${mes}-${anio}`;
                }
            },
            { data: 'action', orderable: false, searchable: false }
        ]
    });


    setInterval(function () {
        refreshTable();
    }, 5000);
});



    function refreshTable() {
        var table = $('#datatable').DataTable();
        table.ajax.reload(null, false);
    }

  function confirmDelete(id) {
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
            const form = document.getElementById("delete-form-" + id);
            const formData = new FormData(form); // Incluye CSRF y _method

            $.ajax({
                url: form.action,
                type: 'POST', // Laravel entiende DELETE por el campo _method
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            // 🔁 Aquí va la recarga de la tabla
                            if (typeof table !== 'undefined') {
                                table.ajax.reload(null, false);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '¡Advertencia!',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr) {
                    let res = xhr.responseJSON;
                    let message = res && res.message
                        ? res.message
                        : 'Ha ocurrido un error al eliminar el modelo.';

                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: message,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
}


   

</script>

@stop