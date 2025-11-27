{!! Form::open(['route' => 'admin.maintenances.store', 'method' => 'POST']) !!}
@include('admin.maintenances.template.form')
<button type="submit" class="btn btn-success">
    <i class="fas fa-save"></i> Registrar
</button>
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
{!! Form::close() !!}
