{!! Form::model($maintenance, ['route' => ['admin.maintenances.update', $maintenance], 'method' => 'PUT']) !!}
@include('admin.maintenances.template.form')
<button type="submit" class="btn btn-success">
    <i class="fas fa-save"></i> Actualizar
</button>
<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
{!! Form::close() !!}
