
    {!! Form::model($model, ['route' => ['admin.models.update', $model->id], 'files' => true , 'method' => 'PUT']) !!}
        @include('admin.models.templantes.form')
       <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save"></i> Guardar</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-ban"></i> Cancelar</button>
       </div>
    {!! Form::close() !!}
