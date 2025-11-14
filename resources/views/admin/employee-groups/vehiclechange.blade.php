{{Form::model($employeeGroup, ['route' => ['admin.employee-groups.vehiclechangeupdate', $employeeGroup->id], 'method' => 'PUT'])}}
<div>
    <p>Grupo de Personal: {{ $employeeGroup->name }}</p>
</div>
<div class="row">
    <div class="col-md-6">
    <select id="vehicleTypeSelect" class="form-control">
        <option value="">Seleccione un tipo de vehículo</option>
    
        @foreach ($vehicletypes as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
        @endforeach
    </select>
    </div>
    <div class="col-md-6">
        <select id="vehicleSelect" name="vehicle_id" class="form-control">
            <option value="">Seleccione un vehículo</option>
        </select>
    </div>
</div>

<div class="d-flex justify-content-end mt-2">
    <button type="submit" class="btn btn-success">Asignar</button>
</div>

{{Form::close()}}

<script>
    $('#vehicleTypeSelect').on('change', function () {
        const typeId = $(this).val();
        $.get('{{ route('admin.vehicles.bytype', 'typeId') }}'.replace('typeId', typeId), function (vehicles) {
            const $vehicleSelect = $('#vehicleSelect');
            $vehicleSelect.empty().append('<option value="">Seleccione un vehículo</option>');
            vehicles.forEach(v => {
                $vehicleSelect.append(`<option value="${v.id}">${v.code} (Capacidad: ${v.people_capacity})</option>`);
            });
        });
    });

    $(document).on('submit', 'form', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PUT');
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                window.location.href = '{{ route('admin.schedulings.create') }}';
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

</script>