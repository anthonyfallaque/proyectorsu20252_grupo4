@extends('adminlte::page')

@section('title', 'Motivos')

@section('content_header')

@stop

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade" id="modalReason" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
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
        <h3 class="card-title">Lista de Motivos</h3>
        <div class="card-tools">
            <button id="btnNewReason" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Nuevo Motivo</button>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped" id="datatable" style="width:100%">
            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
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
    let table;

    $(document).ready(function() {
        table = $('#datatable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            },
            "processing": true,
            "serverSide": true,
            "ajax": "{{ route('admin.reasons.index') }}",
            "columns": [{
                    "data": "name"
                },
                {
                    "data": "description"
                },
                {
                    "data": "action",
                    "orderable": false,
                    "searchable": false
                }
            ]
        });
    });

    $('#btnNewReason').click(function() {
        $.ajax({
            url: "{{ route('admin.reasons.create') }}",
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Nuevo Motivo');
                $('#modalReason .modal-body').html(response);
                $('#modalReason').modal('show');

                $('#modalReason form').submit(function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var formData = form.serialize();
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                $('#modalReason').modal('hide');
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        },
                        error: function(xhr) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                html: errorMessage,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                });
            }
        });
    });

    $(document).on('click', '.btnEditar', function() {
        var reasonId = $(this).attr('id');
        $.ajax({
            url: "{{ route('admin.reasons.edit', 'id') }}".replace('id', reasonId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Editar Motivo');
                $('#modalReason .modal-body').html(response);
                $('#modalReason').modal('show');

                $('#modalReason form').submit(function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var formData = form.serialize();
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                $('#modalReason').modal('hide');
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        },
                        error: function(xhr) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                html: errorMessage,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                });
            }
        });
    });

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
                $.ajax({
                    url: $("#delete-form-" + id).attr('action'),
                    type: 'POST',
                    data: $("#delete-form-" + id).serialize(),
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    },
                    error: function(xhr) {
                        let mensaje = 'Ha ocurrido un error al eliminar el motivo.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensaje = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: mensaje,
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