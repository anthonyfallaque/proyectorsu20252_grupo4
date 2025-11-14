@extends('adminlte::page')

@section('title', 'Vacaciones')

@section('content_header')
@stop

@section('content')
<div class="p-2"></div>



<div class="modal fade" id="modalVacation" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
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
        <h3 class="card-title">Lista de Solicitudes de Vacaciones</h3>
        <div class="card-tools">
            <button id="btnNewVacation" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Nueva Solicitud</button>
        </div>
    </div>



    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filter_start_date">Fecha de inicio:</label>
                <input type="date" id="filter_start_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="filter_end_date">Fecha de fin:</label>
                <input type="date" id="filter_end_date" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button id="btnFilter" class="btn btn-outline-info" style="width: 42px; height: 38px;">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-sm" id="datatable" style="width:100%">
                <thead>
                    <tr>
                        <th style="min-width: 150px;">EMPLEADO</th>
                        <th style="min-width: 100px;">FECHA DE INICIO</th>
                        <th style="min-width: 80px;">SOLICITADO</th>
                        <th style="min-width: 100px;">FECHA FINAL</th>
                        <th style="min-width: 120px;">DÍAS DISPONIBLES</th>
                        <th style="min-width: 90px;">ESTADO</th>
                        <th style="min-width: 150px;">NOTAS</th>
                        <th style="min-width: 120px;">ACCIÓN</th>
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
            "responsive": true,
            "scrollX": true,
            "autoWidth": false,
            "ajax": {
                "url": "{{ route('admin.vacations.index') }}",
                "type": "GET",
                "data": function(d) {
                    d.start_date = $('#filter_start_date').val();
                    d.end_date = $('#filter_end_date').val();
                }
            },
            "columns": [{
                    "data": "employee_name",
                    "name": "employee_name"
                },
                {
                    "data": "request_date_formatted",
                    "name": "request_date"
                },
                {
                    "data": "requested_days",
                    "name": "requested_days",
                    "className": "text-center"
                },
                {
                    "data": "end_date_formatted",
                    "name": "end_date"
                },
                {
                    "data": "current_available_days",
                    "name": "current_available_days",
                    "className": "text-center"
                },
                {
                    "data": "status_badge",
                    "name": "status",
                    "orderable": false,
                    "className": "text-center"
                },
                {
                    "data": "notes",
                    "name": "notes"
                },
                {
                    "data": "action",
                    "name": "action",
                    "orderable": false,
                    "searchable": false,
                    "className": "text-center"
                }
            ],
            "order": [
                [1, "desc"]
            ],
            "pageLength": 10
        });

        $('#btnNewVacation').click(function() {
            $('#modalVacation .modal-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Cargando...</p></div>');

            $.ajax({
                url: "{{ route('admin.vacations.create') }}",
                type: "GET",
                cache: false,
                success: function(response) {
                    $('#ModalLongTitle').text('Nueva Solicitud de Vacaciones');
                    $('#modalVacation .modal-body').html(response);
                    $('#modalVacation').modal('show');

                    initVacationForm();
                    setupFormSubmit($('#modalVacation form'));
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: 'No se pudo cargar el formulario de vacaciones.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });

        function initVacationForm() {
            if ($.fn.datepicker) {
                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    language: 'es',
                    startDate: new Date(new Date().setDate(new Date().getDate() + 11))
                });
            }
            $('#employee_id').off('change').on('change', function() {
                var employeeId = $(this).val();
                var isEdit = $('#vacationForm').attr('action').includes('update');
                var originalEmployeeId = isEdit ? '{{ isset($vacation) ? $vacation->employee_id : "" }}' : '';

                if (employeeId) {
                    $('.available-days-info').text('Verificando días disponibles...');

                    $.ajax({
                        url: "{{ route('admin.vacations.check-available-days') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            employee_id: employeeId,
                            is_edit: isEdit,
                            vacation_id: isEdit ? $('#vacationForm').attr('action').split('/').pop() : null
                        },
                        success: function(response) {
                            if (response.success) {
                                if (isEdit && employeeId != originalEmployeeId) {
                                    var requestedDays = parseInt($('#requested_days').val() || 0);
                                    $('#available_days').val(response.available_days - requestedDays);
                                } else {
                                    $('#available_days').val(response.available_days);
                                }
                                $('.available-days-info').text('Días disponibles: ' + response.available_days);
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Advertencia',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                                $('#available_days').val(0);
                                $('.available-days-info').text('Días disponibles: 0');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error al verificar días disponibles:', xhr);
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: 'No se pudieron verificar los días disponibles.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                            $('#available_days').val(0);
                            $('.available-days-info').text('Días disponibles: 0');
                        }
                    });
                } else {
                    $('#available_days').val(0);
                    $('.available-days-info').text('Días disponibles: 0');
                }
            });

            $('#requested_days').off('change keyup').on('change keyup', function() {
                calculateEndDate();

                var isEdit = $('#vacationForm').attr('action').includes('update');
                if (isEdit) {
                    var originalDays = parseInt($('input[name="original_requested_days"]').val() || 0);
                    var newDays = parseInt($(this).val() || 0);
                    var currentAvailable = parseInt($('#available_days').val() || 0);
                    var difference = originalDays - newDays;

                    $('#available_days').val(currentAvailable + difference);
                }
            });

            $('#request_date').off('change').on('change', function() {
                calculateEndDate();
            });
        }

        function calculateEndDate() {
            var requestDate = $('#request_date').val();
            var requestedDays = $('#requested_days').val();

            if (requestDate && requestedDays) {
                $.ajax({
                    url: "{{ route('admin.vacations.calculate-days') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        request_date: requestDate,
                        requested_days: requestedDays
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#end_date').val(response.end_date);
                        }
                    }
                });
            }
        }

        function checkAvailableDays(employeeId) {
            $.ajax({
                url: "{{ route('admin.vacations.check-available-days') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    employee_id: employeeId
                },
                success: function(response) {
                    if (response.success) {
                        $('#available_days').val(response.available_days);
                        $('.available-days-info').text('Días disponibles: ' + response.available_days);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Advertencia',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                        $('#available_days').val(0);
                        $('.available-days-info').text('Días disponibles: 0');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: 'No se pudieron verificar los días disponibles.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                    $('#available_days').val(0);
                    $('.available-days-info').text('Días disponibles: 0');
                }
            });
        }

        function setupFormSubmit(form) {
            form.off('submit').on('submit', function(e) {
                e.preventDefault();

                var requestedDays = parseInt($('#requested_days').val());
                var availableDays = parseInt($('#available_days').val());
                var status = $('#status').val();

                if (status === 'Approved' && requestedDays > availableDays) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error de validación!',
                        text: `No se puede aprobar la solicitud. El empleado solo tiene ${availableDays} días disponibles y está solicitando ${requestedDays} días.`,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                    return false;
                }

                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#modalVacation').modal('hide');
                            table.ajax.reload(null, false);
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
                        var errors = xhr.responseJSON?.errors;
                        var errorMessage = '';

                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMessage += value + '<br>';
                            });
                        } else if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else {
                            errorMessage = 'Ha ocurrido un error al guardar la solicitud de vacaciones.';
                        }

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


        $(document).on('click', '.btnEditar', function() {
            var vacationId = $(this).attr('id');
            $('#modalVacation .modal-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Cargando...</p></div>');

            $.ajax({
                url: "{{ route('admin.vacations.edit', 'id') }}".replace('id', vacationId),
                type: "GET",
                cache: false,
                data: {
                    _t: Date.now()
                },
                success: function(response) {
                    $('#ModalLongTitle').text('Editar Solicitud de Vacaciones');
                    $('#modalVacation .modal-body').html(response);
                    $('#modalVacation').modal('show');

                    initVacationForm();
                    setupFormSubmit($('#modalVacation form'));
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: 'No se pudo cargar el formulario para editar la solicitud de vacaciones.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });

        $('#btnRefresh').click(function() {
            table.ajax.reload(null, false);
        });
        $('#btnFilter').click(function() {
            table.ajax.reload();
        });
    });



    function confirmDelete(id) {
        var row = table.row($('#delete-form-' + id).closest('tr')).data();
        var isApproved = row && row.status === 'Approved';

        var message = "¡Este cambio no se puede deshacer!";
        if (isApproved) {
            message += "\n\nNota: Esta vacación está aprobada, por lo que los días serán devueltos al empleado.";
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: message,
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
                            table.ajax.reload(null, false);
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
                        var errorMessage = xhr.responseJSON?.message || 'Ha ocurrido un error al eliminar la solicitud de vacaciones.';
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: errorMessage,
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