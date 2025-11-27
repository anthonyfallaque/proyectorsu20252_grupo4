{!! Form::open(['route' => 'admin.activities.store', 'method' => 'POST', 'files' => true]) !!}
{!! Form::hidden('schedule_id', $schedule->id) !!}
@include('admin.maintenances.activities.template.form')
<button type="submit" class="btn btn-success">
    <i class="fas fa-save"></i> Registrar
</button>
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
{!! Form::close() !!}
