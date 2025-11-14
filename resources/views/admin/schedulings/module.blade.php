@extends('adminlte::page')

@section('title', 'Portal de Programación')


@section('content')
    <div class="modal fade" id="modalScheduling" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="ModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
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


    <div class="pt-2 container-fluid" style="height: calc(100vh - 100px); overflow-y: auto;">
        <div class="pb-2 mb-4 d-flex justify-content-between align-items-center border-bottom">
            <h1 class="h4">Programación</h1>
            <!-- Botón de refrescar -->
            <div>
                <button class="btn btn-secondary refresh-btn" onclick="loadData()">
                    <i class="fas fa-sync-alt"></i> Refrescar
                </button>
                <a href="{{ route('admin.schedulings.index') }}" class="btn btn-link">
                    Volver
                </a>
            </div>
        </div>

        <div class="mb-4 row align-items-center">
            <div class="col-md-3">
                <label for="date-select">Seleccione una fecha:</label>
                <input type="date" id="date-select" class="form-control"
                    value="{{ \Carbon\Carbon::today()->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label for="turn-select">Seleccione un turno:</label>
                <select class="form-control" id="turn-select">
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-between">
                <button id="search-btn" class="btn btn-primary">Buscar programación</button>
            </div>
        </div>
        <hr>

        <!-- Aquí va el resto del contenido y resumen -->
        <div class="mb-4 row">
            <!-- Resumen en cuadritos -->
            <div class="col-md-6">
                <div class="row">
                    <div class="mb-3 col-6">
                        <div class="text-center shadow-sm card">
                            <div class="p-3 card-body">
                                👥<div id="attendance-count" class="mb-1 h4">00</div>
                                <small class="text-muted">Asistencias</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 col-6">
                        <div class="text-center shadow-sm card ">
                            <div class="p-3 card-body">
                                🚚
                                <div id="completed-groups" class="mb-1 h4">00</div>
                                <small class="text-muted">Grupos completos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center shadow-sm card ">
                            <div class="p-3 card-body">
                                🧍
                                <div id="available-support" class="mb-1 h4">00</div>
                                <small class="text-muted">Apoyos disponibles</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center shadow-sm card ">
                            <div class="p-3 card-body">
                                ❌
                                <div id="missing-count" class="mb-1 h4">00</div>
                                <small class="text-muted">Faltan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="legend h-100">
                    <strong>Leyenda de colores:</strong>
                    <div class="mt-2 legend-item">
                        <span class="color-box green-box"></span> Grupo completo y listo para operar
                    </div>
                    <div class="legend-item">
                        <span class="color-box red-box"></span> Faltan integrantes por llegar o confirmar asistencia
                    </div>
                </div>
            </div>
        </div>

        <!-- Aquí los cards con vehículos -->
        <div class="row" id="zonas">

        </div>
    </div>
@stop

@section('css')
    <style>
        body .summary-row {
            background-color: #fff;
            border-left: 5px solid #17a2b8;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .summary-row .item {
            font-weight: 500;
        }

        .legend {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #fff;
            border-left: 5px solid #007bff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-width: 600px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .color-box {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border-radius: 3px;
        }

        .green-box {
            background-color: #28a745;
        }

        .red-box {
            background-color: #dc3545;
        }

        .card.vehicle-card {
            height: 160px;
            border-width: 2px;
            border-style: solid;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease-in-out;
            cursor: pointer;
        }

        .card.vehicle-card:hover {
            transform: translateY(-4px);
        }

        .card.vehicle-card.green {
            border-color: #28a745;
        }

        .card.vehicle-card.red {
            border-color: #dc3545;
        }

        .vehicle-title {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .refresh-btn {
            margin-left: 10px;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Función para manejar el click en los cards
        function cardClicked(plate) {
            console.log("Card seleccionado: " + plate);
        }

        // Seleccionar automáticamente el turno según la hora actual
        $(document).ready(function() {
            var hour = new Date().getHours();
            var turnSelect = $('#turn-select');

            if (hour >= 6 && hour < 12) {
                turnSelect.val(1);
            } else if (hour >= 1 && hour < 18) {
                turnSelect.val(2);
            } else {
                turnSelect.val(3);
            }

            loadData(); // Cargar datos al iniciar

            $('#search-btn').click(function() {
                loadData();
            });
        });

        function loadData() {
            var date = $('#date-select').val();
            var turn = $('#turn-select').val();

            // Mostrar un mensaje de carga
            Swal.fire({
                title: 'Cargando Datos...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Realizar la solicitud AJAX
            $.ajax({
                url: '{{ route('admin.schedulings.getDatascheduling') }}', // Actualiza con la ruta de tu servidor
                type: 'GET',
                data: {
                    date: date,
                    turn: turn
                },
                success: function(response) {
                    Swal.close(); // Cerrar el mensaje de carga
                    console.log('Vehículos cargados:', response);
                    let countAttendance = response.countAttendance;
                    let completedGroups = response.completedGroups;
                    let availableSupport = response.availableSupport;
                    let missing = response.missing;
                    $('#attendance-count').text(countAttendance);
                    $('#completed-groups').text(completedGroups);
                    $('#available-support').text(availableSupport);
                    $('#missing-count').text(missing);
                    // Aquí puedes actualizar los cards con los vehículos obtenidos
                    $('#zonas').empty();

                    // Generar tarjetas de zonas
                    response.zonas.forEach((zona, index) => {
                        // Crear el card para la zona
                        let zoneCard = `
                        <div class="mb-4 col-md-3">
                            <div class="card vehicle-card ${zona.status == 'completa' ? 'green' : 'red'}" onclick="cardClicked('${zona.name}')">
                                <div class="text-center card-body">
                                    <div class="vehicle-title">Zona: ${zona.name}</div>
                                    <div>${zona.status === 'completa' ? 'Grupo completo y listo para operar' : 'Faltan integrantes por registrar asistencia'}</div>
                                    <!-- Agregar el botón de editar solo si la zona está incompleta -->
                                    ${zona.status === 'incompleta' ?
                                        `<button class="btn btn-warning btn-sm btnEditar" alt="Reprogramar" id="${zona.scheduling_id}">
                                                <i class="fas fa-retweet"></i>
                                            </button>`
                                        : ''
                                    }
                                </div>
                            </div>
                        </div>
                    `;

                        // Añadir el card de la zona al contenedor de zonas
                        $('#zonas').append(zoneCard);
                    });

                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.error('Error al cargar los datos:', error);
                    Swal.fire('Error', 'Hubo un problema al cargar los datos', 'error');
                }
            });
        }

        $(document).on('click', '.btnEditar', function() {
            var schedulingId = $(this).attr('id');
            $.ajax({
                url: '{{ route('admin.schedulings.editModule', 'id') }}'.replace('id', schedulingId),
                type: "GET",
                success: function(response) {
                    $('#ModalLongTitle').text('Editar Programación');
                    $('#modalScheduling .modal-body').html(response);
                    $('#modalScheduling').modal('show');
                }
            });
        });
    </script>
@stop
