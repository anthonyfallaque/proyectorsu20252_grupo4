<form action="{{ route('admin.shifts.store') }}" method="POST">
    @csrf
    
    <div class="form-group">
        <label for="name">Nombre del Turno <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Ingrese el nombre del turno" required>
        <small class="form-text text-muted">Ejemplo: Turno Mañana, Turno Tarde, Turno Noche</small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="hour_in">Hora de Entrada <span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="hour_in" name="hour_in" required>
                <small class="form-text text-muted">Formato de 24 horas</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="hour_out">Hora de Salida <span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="hour_out" name="hour_out" required>
                <small class="form-text text-muted">Formato de 24 horas</small>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Descripción</label>
        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Ingrese una descripción del turno (opcional)"></textarea>
        <small class="form-text text-muted">Descripción de las características del turno</small>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Nota:</strong> Configure los horarios de entrada y salida para este turno.
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
</form>

<script>
// Validación para que la hora de salida sea posterior a la de entrada
$(document).ready(function() {
    $('#hour_in, #hour_out').on('change', function() {
        var hourIn = $('#hour_in').val();
        var hourOut = $('#hour_out').val();
        
        if (hourIn && hourOut && hourOut <= hourIn) {
            Swal.fire({
                icon: 'warning',
                title: 'Horario inválido',
                text: 'La hora de salida debe ser posterior a la hora de entrada',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            });
            $('#hour_out').val('');
        }
    });
});
</script>