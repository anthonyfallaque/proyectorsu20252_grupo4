@extends('adminlte::page')

@section('title', 'Programaciones')

@section('css')
    <style>
        /* Modificar la altura del contenedor de selección */
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            /* Asegúrate de usar !important si es necesario */
            padding: 6px 12px;
        }

        /* Cambiar el color de fondo del dropdown */
        .select2-container--default .select2-dropdown {
            background-color: #f8f9fa !important;
            /* Fondo claro */
            border-radius: 4px;
        }

        /* Cambiar el color de texto del ítem seleccionado */
        .select2-container--default .select2-selection__rendered {
            color: #333 !important;
            /* Cambiar el color del texto */
        }
    </style>

@stop

@section('content')
    <div class="p-2"></div>

    <!-- Modal -->
    <div class="modal fade" id="modalScheduling" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="ModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
            <h3 class="card-title">
                <i class="fas fa-calendar"></i> Programaciones
            </h3>

            <div class="card-tools">
                <a href="{{ route('admin.module') }}" target="_blank" class="btn btn-outline-success"><i
                        class="fas fa-calendar"></i> Ir al modulo</a>
                <a href="{{ route('admin.schedulings.createOne') }}" class="btn btn-primary"><i class="fas fa-plus"></i>
                    Nueva Programación</a>
                <a href="{{ route('admin.schedulings.create') }}" id="btnNewScheduling" class="btn btn-dark"><i
                        class="fas fa-plus"></i>Programación Masiva</a>

            </div>
        </div>


        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Fecha de inicio: <span class="text-danger">*</span></label>
                    <input type="date" value="{{ $fechaActual }}" name="start_date" id="start_date"
                        class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Fecha de fin: </label>
                    <input type="date" name="end_date" id="end_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-info" id="btnFilter">Filtrar</button>
                </div>
            </div>
            <div class=" table-responsive">
                <table class="table table-striped" id="datatableSchedulings" style="width:100%">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>ESTADO</th>
                            <th>ZONA</th>
                            <th>TURNOS</th>
                            <th>VEHICULO</th>
                            <th>GRUPO</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Si usas serverSide, queda vacío --}}
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {

            var table = $('#datatableSchedulings').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.schedulings.index') }}",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [{
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge'
                    },
                    {
                        data: 'zone',
                        name: 'zone'
                    },
                    {
                        data: 'shift',
                        name: 'shift'
                    },
                    {
                        data: 'vehicle',
                        name: 'vehicle'
                    },
                    {
                        data: 'group',
                        name: 'group'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ]
            });


            // Eliminar empleado con confirmación
            $(document).on('submit', '.delete', function(e) {
                e.preventDefault();
                let form = $(this);

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡Este cambio no se puede deshacer!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Cancelar',
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
                                table.ajax.reload(null, false);
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

            $(document).on('click', '.btnEditar', function() {
                var schedulingId = $(this).attr('id');
                $.ajax({
                    url: '{{ route('admin.schedulings.edit', 'id') }}'.replace('id', schedulingId),
                    type: "GET",
                    success: function(response) {
                        $('#ModalLongTitle').text('Editar Programación');
                        $('#modalScheduling .modal-body').html(response);
                        $('#modalScheduling').modal('show');
                    }
                });
            });



            $(document).on('click', '.btnVer', function() {
                var schedulingId = $(this).attr('id');
                $.ajax({
                    url: '{{ route('admin.schedulings.show', 'id') }}'.replace('id', schedulingId),
                    type: "GET",
                    success: function(response) {
                        $('#ModalLongTitle').text('Ver Programación');
                        $('#modalScheduling .modal-body').html(response);
                        $('#modalScheduling').modal('show');
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


                if (endDate !== '') {

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
                    table.ajax.reload();
                    return;
                }

                table.ajax.reload();

            });



        });
    </script>
@stop
