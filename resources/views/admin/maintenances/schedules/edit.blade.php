{!! Form::model($schedule, ['route' => ['admin.schedules.update', $schedule], 'method' => 'PUT']) !!}
{!! Form::hidden('maintenance_id', $maintenance->id) !!}
@include('admin.maintenances.schedules.template.form')
<button type="submit" class="btn btn-success">
    <i class="fas fa-save"></i> Actualizar
</button>
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
{!! Form::close() !!}
