@extends('adminlte::page')

@section('title', 'Horarios de Mantenimiento')

@section('content_header')
    <a href="{{ route('admin.maintenances.index') }}" class="btn btn-dark">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    <button type="button" class="float-right btn btn-primary" id="btnRegistrarSchedule">
        <i class="fas fa-solid fa-plus"></i> Nuevo
    </button>
    <h1>Horarios de Mantenimiento: {{ $maintenance->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="tableSchedules">
                <thead>
                    <tr>
                        <th>DÍA</th>
                        <th>VEHÍCULO</th>
                        <th>RESPONSABLE</th>
                        <th>TIPO</th>
                        <th>INICIO</th>
                        <th>FIN</th>
                        <!--<th>ESTADO</th>-->
                        <th>VER</th>
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
    <div class="modal fade" id="modalSchedule" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Formulario de Horarios</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
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
        const maintenanceId = {{ $maintenance->id }};

        $(document).on('submit', '.frmDeleteSchedule', function(e) {
            e.preventDefault();
            let form = $(this);

            Swal.fire({
                title: "¿Estás seguro de eliminar?",
                text: "Se eliminarán también todos los días generados para este horario",
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
                            refreshTableSchedules();
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

        $('#btnRegistrarSchedule').click(function() {
            $.ajax({
                url: "{{ route('admin.schedules.create') }}",
                type: "GET",
                data: {
                    maintenance_id: maintenanceId
                },
                success: function(response) {
                    $('#modalSchedule .modal-body').html(response);
                    $('#modalSchedule .modal-title').text("Nuevo horario");
                    $('#modalSchedule').modal('show');

                    $('#modalSchedule form').on('submit', function(e) {
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
                                $('#modalSchedule').modal('hide');
                                refreshTableSchedules();
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

        $(document).on('click', '.btnEditSchedule', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.schedules.index') }}" + "/" + id + "/edit";

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    $('#modalSchedule .modal-body').html(response);
                    $('#modalSchedule .modal-title').text('Editar horario');
                    $('#modalSchedule').modal('show');

                    $('#modalSchedule form').on("submit", function(e) {
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
                                $('#modalSchedule').modal('hide');
                                refreshTableSchedules();
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

        $(document).on('click', '.btnActivities', function() {
            let id = $(this).data('id');
            window.location.href = "{{ route('admin.activities.index') }}?schedule_id=" + id;
        });

        $(document).ready(function() {
            $('#tableSchedules').DataTable({
                "ajax": {
                    "url": "{{ route('admin.schedules.index') }}",
                    "data": {
                        maintenance_id: maintenanceId
                    }
                },
                "columns": [{
                        "data": "day_of_week"
                    },
                    {
                        "data": "vehicle_name"
                    },
                    {
                        "data": "responsible_name"
                    },
                    {
                        "data": "maintenance_type"
                    },
                    {
                        "data": "start_time"
                    },
                    {
                        "data": "end_time"
                    },
                    {
                        "data": "activities",
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

        function refreshTableSchedules() {
            var table = $('#tableSchedules').DataTable();
            table.ajax.reload(null, false);
        }
    </script>
@endsection

@section('css')
@stop
