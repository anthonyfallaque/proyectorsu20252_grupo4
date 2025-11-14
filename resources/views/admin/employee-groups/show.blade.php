
<div class="container">
    <h2>Grupo: {{ $employeeGroup->name }}</h2>

    <div class="card mt-4">
        <div class="card-header">
            <strong>Conductor</strong>
        </div>
        <div class="card-body">
            @if($employeeGroup->conductors->isNotEmpty())
                <ul>
                    @foreach($employeeGroup->conductors as $conductor)
                        <li>{{ $conductor->full_name }}</li>
                    @endforeach
                </ul>
            @else
                <p>No se ha asignado conductor.</p>
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <strong>Ayudantes</strong>
        </div>
        <div class="card-body">
            @if($employeeGroup->helpers->isNotEmpty())
                <ul>
                    @foreach($employeeGroup->helpers as $helper)
                        <li>{{ $helper->full_name }}</li>
                    @endforeach
                </ul>
            @else
                <p>No se han asignado ayudantes.</p>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-ban"></i> Cerrar</button>
   </div>
</div>

