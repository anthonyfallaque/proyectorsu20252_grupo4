<!-- Modal -->
<style>
    /* Modificar la altura del contenedor de selección */
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px) !important; /* Asegúrate de usar !important si es necesario */
        padding: 6px 12px;
    }

    /* Cambiar el color de fondo del dropdown */
    .select2-container--default .select2-dropdown {
        background-color: #f8f9fa !important;  /* Fondo claro */
        border-radius: 4px;
    }

    /* Cambiar el color de texto del ítem seleccionado */
    .select2-container--default .select2-selection__rendered {
        color: #333 !important;  /* Cambiar el color del texto */
    }
</style>


<div class="modal fade" id="modalForm" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
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
    <div class="col-md-4 mb-3">
        <label for="start_date" class="form-label">Fecha de inicio: <span class="text-danger">*</span></label>
        <input type="date" name="start_date" id="start_date" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
        <label for="end_date" class="form-label">Fecha de fin: </label>
        <input type="date" name="end_date" id="end_date" class="form-control">
    </div>
    <div class="col-md-2 mb-3 d-flex align-items-end">
        <button class="btn btn-outline-info w-100" id="btnValidar"> <i class="fas fa-calendar"></i> Validar Disponibilidad</button>
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

        <div class="col-md-4 mb-3 group-card" data-group-id="{{ $group->id }}">
            <div class="card border border-black shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="text-black">{{ $group->name }}</strong>
                    <button class="btn btn-sm btn-danger remove-card ml-auto"><i class="fas fa-trash"></i></button>
                </div>
                <div class="card-body">
                    <p><strong>Zona:</strong> {{ $zone->name ?? 'Sin asignar' }}</p>
                    <p><strong>Turno:</strong> {{ $shift->name }}</p>
                    <p><strong>Dias:</strong> {{ $group->days }}</p>
                    <p><strong>Vehículo:</strong> {{ $vehicle->code ?? 'Sin asignar' }} (Capacidad: {{ $capacity }})
                    <button class="btn btn-sm btn-warning"><i class="fas fa-recycle"></i></button></p>
                    
                    <div class="mb-2">
                        <label><strong>Conductor:</strong></label>
                        <select name="driver_id[{{ $group->id }}]" class="form-control">
                            <option value="">Seleccione un conductor</option>
                            @foreach ($employeesConductor as $emp)
                                <option value="{{ $emp->id }}" {{ $conductor && $conductor->id === $emp->id ? 'selected' : '' }}>
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
    <button type="button" class="btn btn-success" id="submitAll">Registrar Programación</button>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function getGroupData() {
        const groupData = {
            groups: [] // Creamos un array de grupos
        };
        
        // Recorremos todos los grupos
        $('.group-card').each(function () {
            const groupId = $(this).data('group-id');
            const driverId = $(this).find(`select[name="driver_id[${groupId}]"]`).val();
            const helpers = $(this).find(`select[name="helpers[${groupId}][]"]`).map(function () {
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
        groupData.end_date = $('#end_date').val() || null;  // Si no hay end_date, lo ponemos como vacío

        // Imprimimos el JSON en consola para verificar
       

        return groupData;
    }

    function getHelpersData() {
        const data = [];  // Usamos un array para almacenar los datos de todos los grupos
        
        // Recorremos todos los grupos
        $('.group-card').each(function () {
            const groupId = $(this).data('group-id');
            
            // Obtenemos el ID del conductor (driver)
            const driverId = $(this).find(`select[name="driver_id[${groupId}]"]`).val();
            
            // Obtenemos los helpers (ayudantes)
            const helpers = $(this).find(`select[name="helpers[${groupId}][]"]`).map(function () {
                return $(this).val();
            }).get();
            
            // Empujamos el array helpers con driver_id incluido
            data.push(driverId, ...helpers);
        });

        return data;  // Devolvemos el array con los datos
    }

    $(document).on('click', '.remove-card', function () {
        $(this).closest('.group-card').remove();
    });

    initializeDynamicSelects();

    function initializeDynamicSelects() {
         $('select').select2({
            placeholder: 'Seleccione una opción',
        });
    }


    $('#submitAll').on('click', function () {
        if (!validarDates()) {
            return;
        }
        if (!validarSelects()) return;
        const data = getGroupData();
        data._token = '{{ csrf_token() }}';
        Swal.fire({
            title:'Estamos registrando la programación',
            text: 'Por favor, espere un momento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        })

        console.log(JSON.stringify(data));
        $.ajax({
            url: '{{ route('admin.schedulings.store') }}',
            type: 'POST',
            data: data,
            success: function(response) {
                console.log('Datos enviados correctamente', response);
                const noregistros = response.noregistros;  // Lista de grupos no registrados
                const failedGroups = data.groups.filter(group =>
                    noregistros.includes(String(group.employee_group_id))
                );

                console.log('Tamaño:', noregistros.length);

                // Crear un JSON con los grupos que no se registraron
                const failedGroupsJson = {
                    failed_groups: failedGroups,
                    start_date: data.start_date,
                    end_date: data.end_date || null,  // Si no hay end_date, lo ponemos como vacío
                };

                console.log("Grupos no registrados: ", failedGroupsJson);

                if (noregistros.length > 0) {
                    Swal.close();
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Atención!',
                        text: `Se han registrado programaciones pero no se han podido registrar algunos grupos debido a inconsistencias`,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            localStorage.setItem('failedGroups', JSON.stringify(failedGroupsJson));
                            window.location.href = '{{ route('admin.schedulings.createOne') }}';
                        }
                    });
                }else{
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Programación registrada correctamente',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('admin.schedulings.index') }}';
                        }
                    });
                }

            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                console.log(res);
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: res.message || 'Ocurrió un error',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });

    $(document).on('click', '.btn-warning', function () {
        const groupId = $(this).closest('.group-card').data('group-id');
        console.log(groupId);
        $.ajax({
            url: '{{ route('admin.employee-groups.vehiclechange', 'GROUP_ID') }}'.replace('GROUP_ID', groupId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Cambio de Vehículo');
                $('#modalForm .modal-body').html(response);
                $('#modalForm').modal('show');
                
            }
        })
    });


    $('#btnValidar').on('click', function () {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (!validarDates()) {
            return;
        }

        if (!validarSelects()) return;
        $.ajax({
            url: '{{ route('admin.schedulings.validationVacations') }}',
            type: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate,
                helpers: getHelpersData(),

            },
            success: function(response) {
                console.log(response);
                const no_disponibles = response.no_disponibles;

                $('.group-card').each(function () {
                    const groupId = $(this).data('group-id');
                    
                    // Marcar al conductor (driver)
                    const driverId = $(this).find(`select[name="driver_id[${groupId}]"]`).val();
                    if (no_disponibles.includes(parseInt(driverId))) {
                        $(this).find(`select[name="driver_id[${groupId}]"]`).css('border', '2px solid red'); // Borde rojo en conductor
                    } else {
                        $(this).find(`select[name="driver_id[${groupId}]"]`).css('border', ''); // Quitar borde si no está en no_disponibles
                    }

                    // Marcar los ayudantes
                    $(this).find(`select[name="helpers[${groupId}][]"]`).each(function() {
                        const helperId = $(this).val();
                        if (no_disponibles.includes(parseInt(helperId))) {
                            $(this).css('border', '2px solid red'); // Borde rojo en ayudante
                        } else {
                            $(this).css('border', ''); // Quitar borde si no está en no_disponibles
                        }
                    });
                });
                
                Swal.fire({
                    icon: 'info',
                    title: 'Validación Completa',
                    text: 'Los conductores y ayudantes seleccionados han sido validados. Los que tienen borde rojo no están disponibles.',
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
    });


    function validarSelects() {
        let esValido = true;
        let idsSeleccionados = [];

        // Limpiar estilos previos
        $('select').css('border', '');

        $('.group-card').each(function () {
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
            $(this).find(`select[name="helpers[${groupId}][]"]`).each(function () {
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

    function validarDates(){
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



        if (endDate !== '' ) {
            
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

