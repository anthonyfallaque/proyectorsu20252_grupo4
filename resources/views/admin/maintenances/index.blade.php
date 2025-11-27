@extends('adminlte::page')

@section('title', 'Mantenimientos')

@section('content_header')
    <button type="button" class="float-right btn btn-primary" id="btnRegistrar">
        <i class="fas fa-solid fa-plus"></i> Nuevo
    </button>
    <h1>Lista de Mantenimientos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="table">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>INICIO</th>
                        <th>FIN</th>
                        <th>HOR</th>
                        <th>EDIT</th>
                        <th>DEL</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Formulario de Mantenimientos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        $(document).on('submit', '.frmDelete', function(e) {
            e.preventDefault();
            let form = $(this);
            Swal.fire({
                title: "¿Estás seguro de eliminar?",
                text: "Esto no se puede deshacer",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'DELETE',
                        data: form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            refreshTable();
                            Swal.fire({
                                title: "Proceso exitoso!",
                                text: response.message,
                                icon: "success",
                                draggable: true
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Error",
                                text: xhr.responseJSON.message,
                                icon: "error",
                                draggable: true
                            });
                        }
                    });
                }
            });
        });

        $('#btnRegistrar').click(function() {
            $.ajax({
                url: "{{ route('admin.maintenances.create') }}",
                type: "GET",
                success: function(response) {
                    $('#modal .modal-body').html(response);
                    $('#modal .modal-title').text("Nuevo mantenimiento");
                    $('#modal').modal('show');

                    $('#modal form').on('submit', function(e) {
                        e.preventDefault();

                        var form = $(this);
                        var formData = new FormData(form[0]);

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $('#modal').modal('hide');
                                refreshTable();
                                Swal.fire({
                                    title: "Proceso exitoso!",
                                    text: response.message,
                                    icon: "success",
                                    draggable: true
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: "Error",
                                    text: xhr.responseJSON.message,
                                    icon: "error",
                                    draggable: true
                                });
                            }
                        });
                    });
                }
            });
        });

        $(document).on('click', '.btnEdit', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.maintenances.index') }}" + "/" + id + "/edit";

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    $('#modal .modal-body').html(response);
                    $('#modal .modal-title').text('Editar mantenimiento');
                    $('#modal').modal('show');

                    $('#modal form').on("submit", function(e) {
                        e.preventDefault();

                        var form = $(this);
                        var formData = new FormData(this);

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            processData: false,
                            contentType: false,
                            data: formData,
                            success: function(response) {
                                $('#modal').modal('hide');
                                refreshTable();
                                Swal.fire({
                                    title: "Proceso exitoso!",
                                    text: response.message,
                                    icon: "success",
                                    draggable: true
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: "Error",
                                    text: xhr.responseJSON.message,
                                    icon: "error",
                                    draggable: true
                                });
                            }
                        });
                    })
                }
            });
        });

        $(document).on('click', '.btnSchedules', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            window.location.href = "{{ route('admin.schedules.index') }}?maintenance_id=" + id;
        });

        $(document).ready(function() {
            $('#table').DataTable({
                "ajax": "{{ route('admin.maintenances.index') }}",
                "columns": [{
                        "data": "name"
                    },
                    {
                        "data": "start_date"
                    },
                    {
                        "data": "end_date"
                    },
                    {
                        "data": "schedules",
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "edit",
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "delete",
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "language": {
                    url: '//cdn.datatables.net/plug-ins/2.3.4/i18n/es-ES.json'
                }
            });
        });

        function refreshTable() {
            var table = $('#table').DataTable();
            table.ajax.reload(null, false);
        }
    </script>
@endsection
@section('css')
@stop
