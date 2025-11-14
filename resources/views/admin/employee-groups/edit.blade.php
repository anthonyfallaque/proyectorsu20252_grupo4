{!! Form::model($employeeGroup, ['route' => ['admin.employeegroups.update', $employeeGroup->id], 'method' => 'PUT']) !!}
    @include('admin.employee-groups.templantes.form')
    <div class="d-flex justify-content-end ">
        <button type="submit" class="btn btn-primary mr-2"> <i class="fas fa-cloud-upload-alt"></i> Actualizar</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"> <i class="fas fa-ban"></i> Cancelar</button>
    </div>
    
{!! Form::close() !!}
