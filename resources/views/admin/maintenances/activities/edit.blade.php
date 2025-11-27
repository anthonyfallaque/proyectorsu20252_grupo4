{!! Form::model($activity, [
    'route' => ['admin.activities.update', $activity],
    'method' => 'PUT',
    'files' => true,
]) !!}
{!! Form::hidden('schedule_id', $schedule->id) !!}
@include('admin.maintenances.activities.template.form')
<button type="submit" class="btn btn-success">
    <i class="fas fa-save"></i> Actualizar
</button>
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
{!! Form::close() !!}
