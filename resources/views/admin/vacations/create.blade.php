<form action="{{ route('admin.vacations.store') }}" method="POST" id="vacationForm">
    @csrf
    @include('admin.vacations.templantes.form')
    <div class="d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save"></i> Guardar</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-ban"></i> Cancelar</button>
    </div>
</form>