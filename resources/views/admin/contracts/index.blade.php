@extends('adminlte::page')

@section('title', 'Contratos')

@section('content_header')
@stop

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade" id="modalContract" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
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
        <h3 class="card-title">Lista de Contratos</h3>
        <div class="card-tools">
            <button id="btnNewContract" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Nuevo Contrato</button>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped" id="datatable" style="width:100%">
            <thead>
                <tr>
                    <th>EMPLEADO</th>
                    <th>TIPO DE CONTRATO</th>
                    <th>FECHA INICIO</th>
                    <th>FECHA FIN</th>
                    <th>SALARIO</th>
                    <th>POSICIÓN</th>
                    <th>DEPARTAMENTO</th>
                    <th>ESTADO</th>
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
            "ajax": {
                "url": "{{ route('admin.contracts.index') }}",
                "cache": false
            },
            "columns": [{
                    "data": "employee_name"
                },
                {
                    "data": "contract_type"
                },
                {
                    "data": "start_date"
                },
                {
                    "data": "end_date"
                },
                {
                    "data": "salary"
                },
                {
                    "data": "position"
                },
                {
                    "data": "department"
                },
                {
                    "data": "status"
                },
                {
                    "data": "action",
                    "orderable": false,
                    "searchable": false
                }
            ]
        });
        var originalVacationDays;

        function initContractForm(isEditMode = false) {
            var contractTypeSelect = $('#contract_type');
            var endDateField = $('#end_date');
            var startDateField = $('#start_date');
            var endDateContainer = $('#end_date_container');
            var vacationDaysField = $('#vacation_days_per_year');
            var vacationDaysContainer = $('#vacation_days_container');
            var vacationDaysInfo = $('#vacation_days_info');

            originalVacationDays = vacationDaysField.val();

            $('.vacation-days-notice').remove();

            if (!isEditMode) {
                var today = new Date().toISOString().split('T')[0];
                startDateField.attr('min', today);
                endDateField.attr('min', today);
            } else {
                startDateField.removeAttr('min');
                endDateField.removeAttr('min');
            }

            function updateEndDateMinimum() {
                var startDate = startDateField.val();
                if (startDate) {
                    endDateField.attr('min', startDate);
                }
            }

            function updateEndDateField() {
                var contractsWithoutEndDate = ['Nombrado', 'Contrato permanente'];

                if (contractsWithoutEndDate.includes(contractTypeSelect.val())) {
                    endDateField.val('');
                    endDateContainer.addClass('d-none');
                    endDateField.prop('required', false);
                } else {
                    endDateContainer.removeClass('d-none');
                    endDateField.prop('required', true);
                    updateEndDateMinimum();
                }
            }

            function updateVacationDays() {
                var contractType = contractTypeSelect.val();

                if (contractType === 'Temporal') {
                    vacationDaysField.val(0);
                    vacationDaysField.prop('readonly', true);
                    vacationDaysInfo.text('Los contratos temporales no tienen días de vacaciones');
                    vacationDaysContainer.addClass('d-none');
                } else if (['Nombrado', 'Contrato permanente'].includes(contractType)) {
                    vacationDaysField.val(30);
                    vacationDaysField.prop('readonly', true);
                    vacationDaysInfo.text('Tipo de contrato con 30 días de vacaciones fijos');
                    vacationDaysContainer.addClass('d-none');
                } else {
                    vacationDaysField.val(originalVacationDays || 15);
                    vacationDaysField.prop('readonly', false);
                    vacationDaysInfo.text('');
                    vacationDaysContainer.removeClass('d-none');
                }
            }

            function toggleTerminationReason() {
                var isActiveCheckbox = $('#is_active');
                var terminationReasonContainer = $('#termination_reason_container');
                var terminationReasonField = $('#termination_reason');

                if (isActiveCheckbox.is(':checked')) {
                    terminationReasonContainer.addClass('d-none');
                    terminationReasonField.val('');
                } else {
                    terminationReasonContainer.removeClass('d-none');
                }
            }

            function updatePositionId() {
                var employeeId = $('#employee_id').val();

                if (employeeId) {
                    $.ajax({
                        url: "{{ route('admin.employees.getposition', '') }}/" + employeeId,
                        type: "GET",
                        success: function(response) {
                            console.log("Position ID recibido:", response.position_id);
                            $('#position_id_input').val(response.position_id);
                        },
                        error: function(xhr) {
                            console.error("Error al obtener la posición:", xhr.responseText);
                            $('#position_id_input').val('');
                        }
                    });
                } else {
                    $('#position_id_input').val('');
                }
            }

            updateEndDateField();
            updateVacationDays();
            toggleTerminationReason();

            setTimeout(updatePositionId, 500);

            contractTypeSelect.on('change', function() {
                updateEndDateField();
                updateVacationDays();
            });

            startDateField.on('change', function() {
                updateEndDateMinimum();
            });

            $('#is_active').on('change', function() {
                toggleTerminationReason();
            });

            $('#employee_id').on('change', function() {
                updatePositionId();
            });
        }




        function setupFormSubmit(form) {
            form.off('submit').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                            $('#modalContract').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: response.message || 'Ha ocurrido un error',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        let errorMessage = 'Ha ocurrido un error';

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
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

        $('#btnNewContract').click(function() {
            $('#modalContract .modal-title').text('Nuevo Contrato');
            $('#modalContract .modal-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Cargando...</p></div>');
            $('#modalContract').modal('show');

            $.ajax({
                url: "{{ route('admin.contracts.create') }}",
                type: "GET",
                success: function(response) {
                    $('#modalContract .modal-body').html(response);
                    initContractForm(false);
                    setupFormSubmit($('#createContractForm'));
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: 'No se pudo cargar el formulario de contrato',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });

        $(document).on('click', '.btnEditar', function() {
            var contractId = $(this).attr('id');
            $('#modalContract .modal-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Cargando...</p></div>');

            $.ajax({
                url: "{{ route('admin.contracts.edit', 'id') }}".replace('id', contractId),
                type: "GET",
                cache: false,
                data: {
                    _t: Date.now()
                },
                success: function(response) {
                    $('#ModalLongTitle').text('Editar Contrato');
                    $('#modalContract .modal-body').html(response);
                    $('#modalContract').modal('show');

                    initContractForm(true);
                    setupFormSubmit($('#modalContract form'));
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: 'No se pudo cargar el formulario para editar el contrato.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });

        $('#btnRefresh').click(function() {
            table.ajax.reload(null, false);
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
                        let errorMessage = 'Ha ocurrido un error al eliminar el contrato.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
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
            }
        });
    }
</script>
@stop