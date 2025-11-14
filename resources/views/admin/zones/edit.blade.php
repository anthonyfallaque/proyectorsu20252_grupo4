
{!! Form::model($zone, ['route' => ['admin.zones.update', $zone], 'method' => 'PUT', 'id' => 'zoneForm']) !!}
    @include('admin.zones.templantes.form')
    <div class="d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary mr-2" id="btnUpdateZone"><i class="fas fa-save"></i> Actualizar</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-ban"></i> Cancelar</button>
    </div>
{!! Form::close() !!}