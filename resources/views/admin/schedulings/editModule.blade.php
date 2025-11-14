<form id="formEditScheduling">
    @csrf
    <input type="hidden" name="scheduling_id" value="{{ $scheduling->id }}">

    <div class="row">
        {{-- CAMBIO DE TURNO --}}
    <div class="col-md-6">
        <label>Cambio de Turno</label>
        <div class="row align-items-end">
            <div class="col-md-4">
                <label>Turno Actual</label>
                <input type="text" class="form-control" value="{{ $scheduling->employeegroup->shift->name ?? 'No asignado' }}" readonly>
            </div>
            <div class="col-md-6">
                <label>Nuevo Turno</label>
                <select id="selectShift" class="form-control">
                    <option value="">Seleccione un nuevo turno</option>
                    @foreach($shifts as $shift)
                        @if($shift->id !== optional($scheduling->employeegroup->shift)->id)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endif
                    @endforeach 
                </select>
            </div>
            <div class="col-md-2 text-right">
                <button type="button" id="addShiftChange" class="btn btn-success btn-block"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
  

    {{-- CAMBIO DE VEHÍCULO --}}
    <div class="col-md-6">
        <label>Cambio de Vehículo</label>
        <div class="row align-items-end">
            <div class="col-md-4">
                <label>Vehículo Actual</label>
                <input type="text" class="form-control" value="{{ $scheduling->employeegroup->vehicle->plate ?? 'No asignado' }}" readonly>
            </div>
            <div class="col-md-6">
                <label>Nuevo Vehículo</label>
                <select id="selectVehicle" class="form-control">
                    <option value="">Seleccione un nuevo vehiculo</option>
                    @foreach($vehicles as $vehicle)
                        @if($vehicle->id !== optional($scheduling->employeegroup->vehicle)->id)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-right">
                <button type="button" id="addVehicleChange" class="btn btn-success btn-block"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
    </div>
    <hr>

    {{-- CAMBIO DE PERSONAL --}}
<div class="form-group">
    <label>Cambio de Personal</label>
    <div class="row">
        {{-- Personal Actual --}}
        <div class="col-md-4">
            <label>Personal Actual</label>
            <select id="selectCurrentEmployee" class="form-control">
                <option value="">Seleccione un personal</option>
                @foreach($personalNoAsistido as $item)
                    <option value="{{ $item->employee->id }}">{{ $item->employee->names }} {{ $item->employee->lastnames }} ({{ $item->employee->employeeType->name }})</option>
                @endforeach
            </select>
        </div>

        {{-- Nuevo Personal Disponible --}}
        <div class="col-md-6">
            <label>Nuevo Personal</label>
            <select id="selectNewEmployee" class="form-control">
                <option value="">Seleccione un nuevo personal</option>
                @foreach($personalDisponible as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->names }} {{ $employee->lastnames }} ({{ $employee->employeeType->name }})</option>
                @endforeach
            </select>
        </div>

        {{-- Botón de agregar cambio --}}
        <div class="col-md-2 text-right align-self-end">
            <button type="button" id="addEmployeeChange" class="btn btn-success btn-block"><i class="fas fa-plus"></i></button>
        </div>
    </div>
</div>
<hr>

<input type="hidden" id="currentShiftId" value="{{ $scheduling->employeegroup->shift_id }}" data-text="{{ $scheduling->employeegroup->shift->name ?? 'No asignado' }}">
<input type="hidden" id="currentVehicleId" value="{{ $scheduling->employeegroup->vehicle_id }}" data-text="{{ $scheduling->employeegroup->vehicle->plate ?? 'No asignado' }}">


<h5>Cambios Registrados</h5>
<table class="table table-bordered" id="tableChanges">
    <thead>
        <tr>
            <th>Tipo de Cambio</th>
            <th>Valor Anterior</th>
            <th>Valor Nuevo</th>
            <th>Notas</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        {{-- Se insertan dinámicamente --}}
    </tbody>
</table>

<div class="d-flex justify-content-end">
    <button type="button" id="btnGuardarCambios"  class="btn btn-primary">Guardar Cambios</button>
</div>

</form>

<script>
    $(document).ready(function () {
        let cambios = [];
    
        function getTextoSelect(id, selector) {
            return $(`${selector} option[value="${id}"]`).text() || 'No asignado';
        }
    
        function agregarCambio(tipo, idAnterior, idNuevo) {
            // Evitar duplicados si no es personal
            if (tipo !== 'Personal') {
                cambios = cambios.filter(c => c.tipo !== tipo);
                $(`#tableChanges tbody tr[data-tipo="${tipo}"]`).remove();
            }
    
            cambios.push({
                tipo: tipo,
                id_anterior: idAnterior,
                id_nuevo: idNuevo
            });
    
            let textoAnterior = '';
            let textoNuevo = '';
    
            if (tipo === 'Turno') {
                textoAnterior = $('#currentShiftId').data('text') || 'No asignado';
                textoNuevo = getTextoSelect(idNuevo, '#selectShift');
            } else if (tipo === 'Vehiculo') {
                textoAnterior = $('#currentVehicleId').data('text') || 'No asignado';
                textoNuevo = getTextoSelect(idNuevo, '#selectVehicle');
            } else if (tipo === 'Personal') {
                textoAnterior = $('#selectCurrentEmployee option:selected').text();
                textoNuevo = $('#selectNewEmployee option:selected').text();
            }
    
            $('#tableChanges tbody').append(`
                <tr data-tipo="${tipo}" data-id-anterior="${idAnterior}" data-id-nuevo="${idNuevo}">
                    <td>${tipo}</td>
                    <td>${textoAnterior}</td>
                    <td>${textoNuevo}</td>
                    <td><textarea class="form-control nota-cambio" rows="3"></textarea></td>
                    <td><button class="btn btn-danger btn-sm btnRemoveChange"><i class="fas fa-trash"></i></button></td>
                </tr>
            `);
        }
    
        // Cambios individuales
        $('#addShiftChange').click(function () {
            let idNuevo = $('#selectShift').val();
            if (!idNuevo) return;
            let idAnterior = $('#currentShiftId').val();
            if (idNuevo == idAnterior) return;
            agregarCambio('Turno', idAnterior, idNuevo);
        });
    
        $('#addVehicleChange').click(function () {
            let idNuevo = $('#selectVehicle').val();
            if (!idNuevo) return;
            let idAnterior = $('#currentVehicleId').val();
            if (idNuevo == idAnterior) return;
            agregarCambio('Vehiculo', idAnterior, idNuevo);
        });
    
        $('#addEmployeeChange').click(function () {
            let idAnterior = $('#selectCurrentEmployee').val();
            let idNuevo = $('#selectNewEmployee').val();
            if (!idAnterior || !idNuevo || idAnterior === idNuevo) return;
    
            let existe = cambios.some(c =>
                c.tipo === 'Personal' &&
                c.id_anterior == idAnterior &&
                c.id_nuevo == idNuevo
            );
            if (existe) return;
    
            agregarCambio('Personal', idAnterior, idNuevo);
        });
    
        // Eliminar cambio
        $(document).on('click', '.btnRemoveChange', function () {
            let row = $(this).closest('tr');
            let tipo = row.data('tipo');
            let idAnterior = row.data('id-anterior');
            let idNuevo = row.data('id-nuevo');
    
            cambios = cambios.filter(c =>
                !(c.tipo === tipo && c.id_anterior == idAnterior && c.id_nuevo == idNuevo)
            );
    
            row.remove();
        });
    
        // Guardar cambios
        $('#btnGuardarCambios').on('click', function (e) {
            e.preventDefault();
    
            $('#tableChanges tbody tr').each(function () {
                let tipo = $(this).data('tipo');
                let idAnterior = $(this).data('id-anterior');
                let idNuevo = $(this).data('id-nuevo');
                let nota = $(this).find('.nota-cambio').val();
    
                let cambio = cambios.find(c =>
                    c.tipo === tipo &&
                    c.id_anterior == idAnterior &&
                    c.id_nuevo == idNuevo
                );
                if (cambio) {
                    cambio.nota = nota;
                }
            });
    
            console.log(cambios); // Verifica que ahora sí aparezcan los datos correctos
    
            Swal.fire({
                title: '¿Confirmar cambios?',
                text: "Se aplicarán los cambios registrados.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('admin.schedulings.add-change') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            scheduling_id: $('input[name="scheduling_id"]').val(),
                            changes: JSON.stringify(cambios)
                        },
                        success: function (response) { 
                            console.log(response);
                            Swal.fire({
                                icon: 'success',
                                title: '¡Cambios guardados!',
                                text: response.message,
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            console.log(xhr);
                            let res = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: res?.message || 'No se pudo guardar',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        });
    });
    </script>
    