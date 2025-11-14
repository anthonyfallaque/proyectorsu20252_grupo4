@extends('adminlte::page')

@section('title', 'Empleados')

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade" id="modalEmployee" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLongTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Contenido del formulario cargado por AJAX --}}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Empleados</h3>
        <div class="card-tools">
            <button id="btnNewEmployee" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Nuevo Empleado
            </button> 
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table" id="datatableEmployees" style="width:100%">
            <thead>
                <tr>
                    <th>FOTO</th>
                    <th>DNI</th>
                    <th>NOMBRE COMPLETO</th>
                    <th>TELÉFONO</th>
                    <th>TIPO</th>
                    <th>ESTADO</th>
                    <th>CREADO</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Configurar token CSRF para todas las peticiones AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Inicializar DataTable
    var table = $('#datatableEmployees').DataTable({
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
            "infoFiltered": "(filtrado de _MAX_ entradas totales)",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": activar para ordenar ascendente",
                "sortDescending": ": activar para ordenar descendente"
            }
        },
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.employees.index') }}",
        columns: [
            { data: 'photo', name: 'photo', orderable: false, searchable: false },
            { data: 'dni', name: 'dni' },
            { data: 'full_name', name: 'full_name', orderable: false, searchable: false },
            { data: 'phone', name: 'phone' },
            { data: 'employee_type_name', name: 'employee_type_name', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']]
    });

    // Nuevo empleado
    $('#btnNewEmployee').click(function() {
        $.ajax({
            url: "{{ route('admin.employees.create') }}",
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Nuevo Empleado');
                $('#modalEmployee .modal-body').html(response);
                $('#modalEmployee').modal('show');

                $('#modalEmployee form').off('submit').on('submit', function(e) {
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
                            $('#modalEmployee').modal('hide');
                            refreshTable();
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        },
                        error: function(xhr) {
                            let res = xhr.responseJSON;
                            let errors = res.errors || {};
                            let errorMessage = res.message || 'Ocurrió un error';
                            
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('\n');
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: errorMessage,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });

    // Editar empleado
    $(document).on('click', '.btnEditar', function() {
        var employeeId = $(this).attr('id');
        $.ajax({
            url: "{{ route('admin.employees.edit', 'id') }}".replace('id', employeeId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Editar Empleado');
                $('#modalEmployee .modal-body').html(response);
                $('#modalEmployee').modal('show');

                $('#modalEmployee form').off('submit').on('submit', function(e) {
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
                            $('#modalEmployee').modal('hide');
                            refreshTable();
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        },
                        error: function(xhr) {
                            let res = xhr.responseJSON;
                            let errors = res.errors || {};
                            let errorMessage = res.message || 'Ocurrió un error';
                            
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('\n');
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: errorMessage,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    });

    // Eliminar empleado
    $(document).on('submit', '.delete', function(e) {
        e.preventDefault();
        let form = $(this);

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
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        refreshTable();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: res?.message || 'Ocurrió un error',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            }
        });
    });

    function refreshTable() {
        var table = $('#datatableEmployees').DataTable();
        table.ajax.reload(null, false);
    }
});
</script>
@stop