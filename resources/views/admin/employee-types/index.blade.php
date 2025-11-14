@extends('adminlte::page')

@section('title', 'Tipos de Empleados')

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade" id="modalEmployeeType" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
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
        <h3 class="card-title">Lista de Tipos de Empleados</h3>
        <div class="card-tools">
            <button id="btnNewEmployeeType" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Nuevo Tipo</button> 
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table" id="datatableEmployeeTypes" style="width:100%">
            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
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
    var table = $('#datatableEmployeeTypes').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.employee-types.index') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']]
    });

    // Nuevo tipo de empleado
    $('#btnNewEmployeeType').click(function() {
        $.ajax({
            url: "{{ route('admin.employee-types.create') }}",
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Nuevo Tipo de Empleado');
                $('#modalEmployeeType .modal-body').html(response);
                $('#modalEmployeeType').modal('show');

                $('#modalEmployeeType form').off('submit').on('submit', function(e) {
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
                            $('#modalEmployeeType').modal('hide');
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

    // Editar tipo de empleado
    $(document).on('click', '.btnEditar', function() {
        var employeeTypeId = $(this).attr('id');
        $.ajax({
            url: "{{ route('admin.employee-types.edit', 'id') }}".replace('id', employeeTypeId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Editar Tipo de Empleado');
                $('#modalEmployeeType .modal-body').html(response);
                $('#modalEmployeeType').modal('show');

                $('#modalEmployeeType form').off('submit').on('submit', function(e) {
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
                            $('#modalEmployeeType').modal('hide');
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

    // Eliminar tipo de empleado
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
        var table = $('#datatableEmployeeTypes').DataTable();
        table.ajax.reload(null, false);
    }
});
</script>
@stop