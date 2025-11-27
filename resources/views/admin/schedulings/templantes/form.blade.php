<!-- Modal -->
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


<div class="modal fade" id="modalForm" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog"
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
<hr>
<div class="row">
    <div class="mb-3 col-md-4">
        <label for="start_date" class="form-label">Fecha de inicio: <span class="text-danger">*</span></label>
        <input type="date" name="start_date" id="start_date" class="form-control">
    </div>
    <div class="mb-3 col-md-4">
        <label for="end_date" class="form-label">Fecha de fin: </label>
        <input type="date" name="end_date" id="end_date" class="form-control">
    </div>
    <div class="mb-3 col-md-2 d-flex align-items-end">
        <button class="btn btn-outline-info w-100" id="btnValidar"> <i class="fas fa-calendar"></i> Validar
            Disponibilidad</button>
    </div>
</div>

<hr>
<div class="row">
    @foreach ($employeeGroups as $group)
        @php
            $vehicle = $vehicles->firstWhere('id', $group->vehicle_id);
            $zone = $zones->firstWhere('id', $group->zone_id);
            $conductor = $group->conductors->first();
            $helpers = $group->helpers;
            $capacity = optional($vehicle)->people_capacity ?? 1;
            $numHelpers = max(0, $capacity - 1);
        @endphp

        <div class="mb-3 col-md-4 group-card" data-group-id="{{ $group->id }}">
            <div class="border border-black shadow-sm card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="text-black">{{ $group->name }}</strong>
                    <button class="ml-auto btn btn-sm btn-danger remove-card"><i class="fas fa-trash"></i></button>
                </div>
                <div class="card-body">
                    <p><strong>Zona:</strong> {{ $zone->name ?? 'Sin asignar' }}</p>
                    <p><strong>Turno:</strong> {{ $shift->name }}</p>
                    <p><strong>Dias:</strong> {{ $group->days }}</p>
                    <p><strong>Vehículo:</strong> {{ $vehicle->code ?? 'Sin asignar' }} (Capacidad: {{ $capacity }})
                        <button class="btn btn-sm btn-warning"><i class="fas fa-recycle"></i></button>
                    </p>

                    <div class="mb-2">
                        <label><strong>Conductor:</strong></label>
                        <select name="driver_id[{{ $group->id }}]" class="form-control">
                            <option value="">Seleccione un conductor</option>
                            @foreach ($employeesConductor as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ $conductor && $conductor->id === $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @for ($i = 0; $i < $numHelpers; $i++)
                        <div class="mb-2">
                            <label>Ayudante {{ $i + 1 }}:</label>
                            <select name="helpers[{{ $group->id }}][]" class="form-control">
                                <option value="">Seleccione un ayudante</option>
                                @foreach ($employeesAyudantes as $ayudante)
                                    <option value="{{ $ayudante->id }}"
                                        {{ isset($helpers[$i]) && $helpers[$i]->id === $ayudante->id ? 'selected' : '' }}>
                                        {{ $ayudante->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row justify-content-end">
    <button type="button" class="btn btn-success" id="submitAll" disabled>Registrar Programación</button>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function getGroupData() {
        const groupData = {
            groups: [] // Creamos un array de grupos
        };

        // Recorremos todos los grupos
        $('.group-card').each(function() {
            const groupId = $(this).data('group-id');
            const driverId = $(this).find(`select[name="driver_id[${groupId}]"]`).val();
            const helpers = $(this).find(`select[name="helpers[${groupId}][]"]`).map(function() {
                return $(this).val();
            }).get();

            // Almacenamos los datos de cada grupo en el array 'groups'
            groupData.groups.push({
                employee_group_id: groupId, // Aquí almacenamos el employee_group_id
                driver_id: driverId,
                helpers: helpers
            });
        });

        // Añadimos las fechas al objeto de datos
        groupData.start_date = $('#start_date').val();
        groupData.end_date = $('#end_date').val() || null; // Si no hay end_date, lo ponemos como vacío

        // Imprimimos el JSON en consola para verificar


        return groupData;
    }

    function getHelpersData() {
        const data = []; // Usamos un array para almacenar los datos de todos los grupos

        // Recorremos todos los grupos
        $('.group-card').each(function() {
            const groupId = $(this).data('group-id');

            // Obtenemos el ID del conductor (driver)
            const driverId = $(this).find(`select[name="driver_id[${groupId}]"]`).val();

            // Obtenemos los helpers (ayudantes)
            const helpers = $(this).find(`select[name="helpers[${groupId}][]"]`).map(function() {
                return $(this).val();
            }).get();

            // Empujamos el array helpers con driver_id incluido
            data.push(driverId, ...helpers);
        });

        return data; // Devolvemos el array con los datos
    }

    $(document).on('click', '.remove-card', function() {
        $(this).closest('.group-card').remove();
    });

    initializeDynamicSelects();

    function initializeDynamicSelects() {
        $('select').select2({
            placeholder: 'Seleccione una opción',
        });
    }


    $('#submitAll').on('click', function() {
        const data = getGroupData();
        data._token = '{{ csrf_token() }}';

        Swal.fire({
            title: 'Guardando programación...',
            text: 'Por favor, espere un momento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route('admin.schedulings.store') }}',
            type: 'POST',
            data: data,
            success: function(response) {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Todas las programaciones se crearon correctamente',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '{{ route('admin.schedulings.index') }}';
                });
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: res.message || 'Ocurrió un error al guardar',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });

    $(document).on('click', '.btn-warning', function() {
        const groupId = $(this).closest('.group-card').data('group-id');
        console.log(groupId);
        $.ajax({
            url: '{{ route('admin.employee-groups.vehiclechange', 'GROUP_ID') }}'.replace('GROUP_ID',
                groupId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Cambio de Vehículo');
                $('#modalForm .modal-body').html(response);
                $('#modalForm').modal('show');

            }
        })
    });


    $('#btnValidar').on('click', function() {
        if (!validarDates()) {
            return;
        }
        if (!validarSelects()) return;

        const data = getGroupData();
        data._token = '{{ csrf_token() }}';

        Swal.fire({
            title: 'Validando disponibilidad...',
            text: 'Por favor, espere un momento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route('admin.schedulings.validate-availability') }}',
            type: 'POST',
            data: data,
            success: function(response) {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: 'Validación Completa',
                    text: response.message,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Habilitar botón de registro
                    $('#submitAll').prop('disabled', false);
                });
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.close();

                if (res.errors && Array.isArray(res.errors) && res.errors.length > 0) {
                    let errorList =
                        '<ul style="text-align: left; max-height: 400px; overflow-y: auto; padding-left: 20px;">';
                    res.errors.forEach(error => {
                        errorList += `<li style="margin-bottom: 8px;">${error}</li>`;
                    });
                    errorList += '</ul>';

                    Swal.fire({
                        icon: 'error',
                        title: 'Problemas Encontrados',
                        html: `<div style="text-align: center;"><p><strong>${res.message}</strong></p></div>${errorList}`,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Aceptar',
                        width: '700px'
                    });

                    // Deshabilitar botón de registro
                    $('#submitAll').prop('disabled', true);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: res.message || 'Ocurrió un error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Aceptar'
                    });
                }
            }
        });
    });


    function validarSelects() {
        let esValido = true;
        let idsSeleccionados = [];

        // Limpiar estilos previos
        $('select').css('border', '');

        $('.group-card').each(function() {
            const groupId = $(this).data('group-id');

            // Validar conductor
            const driverSelect = $(this).find(`select[name="driver_id[${groupId}]"]`);
            const driverId = driverSelect.val();
            if (!driverId) {
                driverSelect.css('border', '2px solid red');
                esValido = false;
            } else if (idsSeleccionados.includes(driverId)) {
                driverSelect.css('border', '2px solid red');
                esValido = false;
            } else {
                idsSeleccionados.push(driverId);
            }

            // Validar ayudantes
            $(this).find(`select[name="helpers[${groupId}][]"]`).each(function() {
                const helperId = $(this).val();
                if (!helperId) {
                    $(this).css('border', '2px solid red');
                    esValido = false;
                } else if (idsSeleccionados.includes(helperId)) {
                    $(this).css('border', '2px solid red');
                    esValido = false;
                } else {
                    idsSeleccionados.push(helperId);
                }
            });
        });

        if (!esValido) {
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: 'Todos los campos deben estar seleccionados y sin duplicados.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            });
        }

        return esValido;
    }

    function validarDates() {
        let esValido = true;
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (startDate === '' || startDate === null) {
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: 'Por favor, selecciona al menos la fecha de inicio.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            });
            esValido = false;
        }



        if (endDate !== '') {

            if (startDate > endDate) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Atención!',
                    text: 'La fecha de fin no puede ser menor que la fecha de inicio.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                });
                esValido = false;
            }
            // Recargar el DataTable con las fechas
        }

        return esValido;

    }
</script>
