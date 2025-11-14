
{!! Form::model($contract, ['route' => ['admin.contracts.update', $contract->id], 'method' => 'PUT', 'id' => 'editContractForm']) !!}
    @include('admin.contracts.templantes.form')
    <div class="d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save"></i> Actualizar</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-ban"></i> Cancelar</button>
    </div>   
{!! Form::close() !!}