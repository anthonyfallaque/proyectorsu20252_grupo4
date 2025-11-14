    <div class="row">
        {{-- Fecha de Inicio y Fin --}}
        <div class="col-md-4">
            <label>Fecha de Inicio</label>
            <input type="date" id="start_date" name="start_date" required class="form-control">
            <div id="start_date_error" class="text-danger" style="display: none;">La fecha de inicio es obligatoria.</div>

        </div>
        <div class="col-md-4">
            <label>Fecha de Fin</label>
            <input type="date" id="end_date" name="end_date" required class="form-control">
            <div id="end_date_error" class="text-danger" style="display: none;">La fecha de fin no puede ser menor a la
                de inicio.</div>

        </div>
        <div class="col-md-4">
            <label>Tipo de Cambio</label>
            <select id="change_type" name="change" class="form-control">
                <option value="">Seleccione el tipo de cambio</option>
                <option value="turno">Cambio de Turno</option>
                <option value="vehiculo">Cambio de Vehículo</option>
                <option value="personal">Cambio de Personal</option>
            </select>
        </div>
    </div>
    <hr>

    {{-- Sección de Cambio de Turno (inicialmente oculta) --}}
    <div id="turnoFields" class="mb-3 changeFields row" style="display: none;">
        <div class="col-md-12">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label>Grupo personal</label>
                    <select id="select_group_turno" name="selectgroup1" class="form-control">
                        <option value="">Seleccione un grupo de empleados</option>
                        @foreach ($employeegroups as $employeegroup)
                            <option value="{{ $employeegroup->id }}">{{ $employeegroup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Nuevo Turno</label>
                    <select id="selectShift" name="selectShift" class="form-control">
                        <option value="">Seleccione un nuevo turno</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección de Cambio de Vehículo (inicialmente oculta) --}}
    <div id="vehiculoFields" class="mb-3 changeFields row" style="display: none;">
        <div class="col-md-12">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label>Vehículo Actual</label>
                    <select id="selectGroupVehicle" name="selectGroupVehicle" class="form-control">
                        <option value="">Seleccione un grupo de empleados</option>
                        @foreach ($employeegroups as $employeegroup)
                            <option value="{{ $employeegroup->id }}">{{ $employeegroup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Nuevo Vehículo</label>
                    <select id="selectVehicle" name="selectVehicle" class="form-control" style="width: 100%">
                        <option value="">Seleccione un nuevo vehiculo</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección de Cambio de Personal (inicialmente oculta) --}}
    <div id="personalFields" class="mb-3 changeFields row" style="display: none;">
        {{-- Personal Actual --}}
        <div class="col-md-6">
            <label>Personal Actual</label>
            <select id="selectCurrentEmployee" name="selectCurrentEmployee" class="form-control">
                <option value="">Seleccione un personal</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->lastnames }} {{ $employee->names }}</option>
                @endforeach
            </select>
        </div>

        {{-- Nuevo Personal Disponible --}}
        <div class="col-md-6">
            <div class="col-md-12">
                <label>Nuevo Personal</label>
            </div>
            <select id="selectNewEmployee" name="selectNewEmployee" class="form-control" style="width: 100%;">
                <option value="">Seleccione un nuevo personal</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->lastnames }} {{ $employee->names }}</option>
                @endforeach
            </select>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            // Inicialización select2
            $('#selectNewEmployee').select2({
                placeholder: 'Seleccione una opción',
                dropdownParent: $('#modalChange'),
            });
            $('#selectVehicle').select2({
                placeholder: 'Seleccione un nuevo vehiculo',
                dropdownParent: $('#modalChange'),
            });

            let cambios = [];

            // Función para mostrar campos según tipo de cambio seleccionado
            $('#change_type').change(function() {
                let tipoCambio = $(this).val();
                $('.changeFields').hide(); // Ocultar todos los campos de cambios

                if (tipoCambio === 'turno') {
                    $('#turnoFields').show();
                } else if (tipoCambio === 'vehiculo') {
                    $('#vehiculoFields').show();
                } else if (tipoCambio === 'personal') {
                    $('#personalFields').show();
                }
            });

            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const startDateError = document.getElementById('start_date_error');
            const endDateError = document.getElementById('end_date_error');

            // Evento cuando la fecha de inicio cambia
            startDateInput.addEventListener('change', function() {
                // Si se selecciona una fecha de inicio, verificamos la fecha de fin
                if (startDateInput.value && !endDateInput.value) {

                } else {
                    // Si la fecha de fin está vacía y la de inicio está seleccionada
                    if (endDateInput.value && new Date(startDateInput.value) > new Date(endDateInput
                            .value)) {
                        endDateInput.style.borderColor = "red"; // Marcar el campo de fecha fin
                        endDateError.style.display = 'block'; // Mostrar el error
                        endDateInput.value = '';
                    } else {
                        endDateInput.style.borderColor = ""; // Limpiar el borde si la validación pasa
                        endDateError.style.display = 'none'; // Ocultar el mensaje de error
                    }
                }
            });

            // Evento cuando la fecha de fin cambia
            endDateInput.addEventListener('change', function() {
                // Si hay una fecha de inicio y se selecciona la fecha de fin
                if (startDateInput.value && new Date(startDateInput.value) > new Date(endDateInput.value)) {
                    endDateInput.style.borderColor = "red"; // Marcar la fecha fin
                    endDateError.style.display = 'block'; // Mostrar el error
                    endDateInput.value = '';
                } else {
                    endDateInput.style.borderColor = ""; // Limpiar el borde
                    endDateError.style.display = 'none'; // Ocultar el mensaje de error
                }
            });



        });
    </script>
