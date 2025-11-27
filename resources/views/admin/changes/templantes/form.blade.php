<div class="row">
    {{-- Primera fila: Fecha Inicio, Fecha Fin, Zonas, Tipo de Cambio --}}
    <div class="col-md-3">
        <label>Fecha de Inicio <span class="text-danger">*</span></label>
        <input type="date" id="start_date" name="start_date" required class="form-control">
    </div>
    <div class="col-md-3">
        <label>Fecha de Fin <span class="text-danger">*</span></label>
        <input type="date" id="end_date" name="end_date" required class="form-control">
    </div>
    <div class="col-md-3">
        <label>Zonas (Opcional)</label>
        <select id="zone_id" name="zone_id" class="form-control">
            <option value="">Seleccione zonas (opcional)</option>
            @foreach ($zones as $zone)
                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Dejar vacío para aplicar a todas las zonas</small>
    </div>
    <div class="col-md-3">
        <label>Tipo de Cambio <span class="text-danger">*</span></label>
        <select id="change_type" name="change_type" class="form-control" required>
            <option value="">Seleccione el tipo de cambio</option>
            <option value="personal">Cambio de Conductor</option>
            <option value="turno">Cambio de Turno</option>
            <option value="vehiculo">Cambio de Vehículo</option>
        </select>
    </div>
</div>

{{-- Sección de Cambio de Personal --}}
<div id="personalFields" class="mt-3 changeFields row" style="display: none;">
    <div class="col-md-6">
        <label>Conductor a Reemplazar <span class="text-danger">*</span></label>
        <select id="selectCurrentEmployee" name="selectCurrentEmployee" class="form-control">
            <option value="">Seleccione un conductor</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}">
                    {{ $employee->lastnames }} {{ $employee->names }} - {{ $employee->document_number }}
                    ({{ $employee->contract_status }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label>Nuevo Conductor <span class="text-danger">*</span></label>
        <select id="selectNewEmployee" name="selectNewEmployee" class="form-control">
            <option value="">Seleccione un conductor</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}">
                    {{ $employee->lastnames }} {{ $employee->names }} - {{ $employee->document_number }}
                    ({{ $employee->contract_status }})
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- Sección de Cambio de Turno --}}
<div id="turnoFields" class="mt-3 changeFields row" style="display: none;">
    <div class="col-md-6">
        <label>Turno Actual <span class="text-danger">*</span></label>
        <select id="select_old_shift" name="select_old_shift" class="form-control">
            <option value="">Seleccione el turno actual</option>
            @foreach ($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label>Nuevo Turno <span class="text-danger">*</span></label>
        <select id="selectShift" name="selectShift" class="form-control">
            <option value="">Seleccione el nuevo turno</option>
            @foreach ($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Sección de Cambio de Vehículo --}}
<div id="vehiculoFields" class="mt-3 changeFields row" style="display: none;">
    <div class="col-md-6">
        <label>Vehículo Actual <span class="text-danger">*</span></label>
        <select id="select_old_vehicle" name="select_old_vehicle" class="form-control">
            <option value="">Seleccione el vehículo actual</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}">{{ $vehicle->plate }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label>Nuevo Vehículo <span class="text-danger">*</span></label>
        <select id="selectVehicle" name="selectVehicle" class="form-control">
            <option value="">Seleccione el nuevo vehículo</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}">{{ $vehicle->plate }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Motivo del Cambio --}}
<div class="mt-3 row">
    <div class="col-md-12">
        <label>Motivo del Cambio Masivo <span class="text-danger">*</span></label>
        <textarea name="motivo" id="motivo" class="form-control" rows="3" placeholder="Permiso por salud" required></textarea>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicialización select2
        $('#selectNewEmployee').select2({
            placeholder: 'Seleccione un conductor',
            dropdownParent: $('#modalChange'),
        });

        $('#selectVehicle').select2({
            placeholder: 'Seleccione un nuevo vehículo',
            dropdownParent: $('#modalChange'),
        });

        // Función para mostrar campos según tipo de cambio seleccionado
        $('#change_type').change(function() {
            let tipoCambio = $(this).val();
            $('.changeFields').hide(); // Ocultar todos los campos

            if (tipoCambio === 'turno') {
                $('#turnoFields').show();
            } else if (tipoCambio === 'vehiculo') {
                $('#vehiculoFields').show();
            } else if (tipoCambio === 'personal') {
                $('#personalFields').show();
            }
        });

        // Validación de fechas
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && new Date(startDateInput.value) > new Date(endDateInput.value)) {
                alert('La fecha de fin no puede ser menor que la fecha de inicio');
                endDateInput.value = '';
            }
        });
    });
</script>
