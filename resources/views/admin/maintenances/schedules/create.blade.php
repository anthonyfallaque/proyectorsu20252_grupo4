{!! Form::open(['route' => 'admin.schedules.store', 'method' => 'POST']) !!}
{!! Form::hidden('maintenance_id', $maintenance->id) !!}
@include('admin.maintenances.schedules.template.form')
<button type="submit" class="btn btn-success">
    <i class="fas fa-save"></i> Registrar
</button>
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
{!! Form::close() !!}
