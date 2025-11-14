{!! Form::open(['route' => ['admin.vehicles.destroy', $vehicle->id], 'method' => 'DELETE']) !!}
    <button type="submit" class="btn btn-danger">Eliminar</button>
{!! Form::close() !!}
