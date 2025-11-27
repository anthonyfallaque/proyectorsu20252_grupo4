@extends('adminlte::page')

@section('title', 'Actividades de Mantenimiento')

@section('content_header')
    <a href="javascript:history.back()" class="btn btn-dark">
        <i class="fas fa-arrow-left"></i> Volver
    </a>

    <h1>Actividades: {{ $schedule->maintenance->name }} - {{ $schedule->day_of_week }} - {{ $schedule->vehicle->name }}
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="tableActivities">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>OBSERVACIÓN</th>
                        <th>IMAGEN</th>
                        <th>EDIT</th>
                        <th>EST</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalActivity" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Formulario de Actividades</h5>
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
        const scheduleId = {{ $schedule->id }};

        $(document).on('click', '.btnEditActivity', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.activities.index') }}" + "/" + id + "/edit";

            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    $('#modalActivity .modal-body').html(response);
                    $('#modalActivity .modal-title').text('Editar actividad');
                    $('#modalActivity').modal('show');

                    // Limpiar eventos anteriores
                    $('#modalActivity form').off('submit');

                    $('#modalActivity form').on("submit", function(e) {
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
                                console.log('Update successful:', response);
                                $('#modalActivity').modal('hide');

                                // Esperar a que se cierre el modal antes de recargar
                                $('#modalActivity').on('hidden.bs.modal',
                                    function() {
                                        refreshTableActivities();
                                        $(this).off(
                                            'hidden.bs.modal'
                                        ); // Limpiar el evento
                                    });

                                Swal.fire({
                                    title: "¡Proceso exitoso!",
                                    text: response.message,
                                    icon: "success", // CAMBIO: type → icon
                                    timer: 2000,
                                    showConfirmButton: true
                                });
                            },
                            error: function(xhr) {
                                console.error('Update error:', xhr);
                                Swal.fire({
                                    title: "Error",
                                    text: xhr.responseJSON ? xhr
                                        .responseJSON.message :
                                        "Error al actualizar",
                                    icon: "error" // CAMBIO: type → icon
                                });
                            }
                        });
                    });
                }
            });
        });

        $(document).ready(function() {
            var table = $('#tableActivities').DataTable({
                "processing": true,
                "serverSide": false,
                "ajax": {
                    "url": "{{ route('admin.activities.index') }}",
                    "type": "GET",
                    "data": {
                        schedule_id: scheduleId
                    },
                    "dataSrc": function(json) {
                        console.log('DataTable data:', json);
                        return json.data;
                    }
                },
                "columns": [{
                        "data": "activity_date"
                    },
                    {
                        "data": "observation",
                        "defaultContent": ""
                    },
                    {
                        "data": "image_preview",
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "edit",
                        "orderable": false,
                        "searchable": false
                    },
                    {
                        "data": "completed_icon",
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                }
            });

            // Guardar referencia global
            window.activitiesTable = table;
        });

        function refreshTableActivities() {
            console.log('Refreshing table...');
            if (window.activitiesTable) {
                window.activitiesTable.ajax.reload(function() {
                    console.log('Table reloaded successfully');
                }, false);
            } else {
                console.error('Table not initialized');
            }
        }
    </script>
@endsection

@section('css')
@stop
