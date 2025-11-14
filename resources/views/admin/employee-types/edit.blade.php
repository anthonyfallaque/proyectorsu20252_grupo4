<form action="{{ route('admin.employee-types.update', $employeeType->id) }}" method="POST" id="employeeTypeEditForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="name">Nombre <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $employeeType->name) }}"
                       placeholder="Conductor, Ayudante, Supervisor..." 
                       maxlength="100"
                       required>
                <div class="invalid-feedback" id="name-error"></div>
                <small class="form-text text-muted">Nombre único del tipo de empleado (solo letras y espacios)</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea class="form-control" 
                          id="description" 
                          name="description" 
                          rows="4" 
                          placeholder="Descripción de las funciones y responsabilidades del tipo de empleado..."
                          maxlength="500">{{ old('description', $employeeType->description) }}</textarea>
                <div class="invalid-feedback" id="description-error"></div>
                <small class="form-text text-muted">Descripción opcional (mínimo 10 caracteres si se proporciona)</small>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="submit-btn">Actualizar</button>
    </div>
</form>

<script>
$(document).ready(function() {
    let validationErrors = {};
    const currentEmployeeTypeId = {{ $employeeType->id }};

    // Configuración de validaciones (igual que create)
    const validationRules = {
        name: {
            required: true,
            pattern: /^[a-zA-ZÀ-ÿ\s]+$/,
            minLength: 2,
            maxLength: 100,
            unique: true,
            message: 'El nombre solo puede contener letras y espacios'
        },
        description: {
            required: false,
            minLength: 10,
            maxLength: 500,
            message: 'La descripción debe tener entre 10 y 500 caracteres'
        }
    };

    // Función principal de validación
    function validateField(fieldName, value) {
        const rule = validationRules[fieldName];
        if (!rule) return true;

        // Campo requerido
        if (rule.required && (!value || value.trim() === '')) {
            setFieldError(fieldName, 'Este campo es obligatorio');
            return false;
        }

        // Si el campo está vacío y no es requerido, es válido
        if (!value || value.trim() === '') {
            if (!rule.required) {
                setFieldValid(fieldName);
                return true;
            }
        }

        // Longitud mínima
        if (rule.minLength && value.length < rule.minLength) {
            setFieldError(fieldName, `Debe tener al menos ${rule.minLength} caracteres`);
            return false;
        }

        // Longitud máxima
        if (rule.maxLength && value.length > rule.maxLength) {
            setFieldError(fieldName, `No puede exceder ${rule.maxLength} caracteres`);
            return false;
        }

        // Patrón
        if (rule.pattern && !rule.pattern.test(value)) {
            setFieldError(fieldName, rule.message);
            return false;
        }

        // Verificar unicidad para edición
        if (rule.unique && value) {
            checkUniqueEdit(fieldName, value);
            return true;
        }

        setFieldValid(fieldName);
        return true;
    }

    // Verificar unicidad para edición
    function checkUniqueEdit(field, value) {
        if (!value) return;
        
        $.ajax({
            url: '/admin/employee-types/check-unique',
            method: 'POST',
            data: {
                field: field,
                value: value,
                employee_type_id: currentEmployeeTypeId, // Excluir tipo actual
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.unique) {
                    setFieldValid(field);
                } else {
                    setFieldError(field, `Ya existe un tipo de empleado con este ${field}`);
                }
            }
        });
    }

    // Marcar campo como válido
    function setFieldValid(fieldName) {
        const field = $(`#${fieldName}`);
        field.removeClass('is-invalid').addClass('is-valid');
        $(`#${fieldName}-error`).hide();
        delete validationErrors[fieldName];
        updateSubmitButton();
    }

    // Marcar campo como inválido
    function setFieldError(fieldName, message) {
        const field = $(`#${fieldName}`);
        field.removeClass('is-valid').addClass('is-invalid');
        $(`#${fieldName}-error`).text(message).show();
        validationErrors[fieldName] = message;
        updateSubmitButton();
    }

    // Actualizar botón de envío
    function updateSubmitButton() {
        const hasErrors = Object.keys(validationErrors).length > 0;
        const requiredFields = ['name'];
        const allRequiredFilled = requiredFields.every(field => {
            const value = $(`#${field}`).val();
            return value && value.trim() !== '';
        });

        const isFormValid = !hasErrors && allRequiredFilled;
        $('#submit-btn').prop('disabled', !isFormValid);
    }

    // Event listeners
    $('input, textarea').on('input change blur', function() {
        const fieldName = $(this).attr('name');
        const value = $(this).val();
        
        if (validationRules[fieldName]) {
            validateField(fieldName, value);
        }
    });

    // Formatear campos automáticamente
    $('#name').on('input', function() {
        let value = $(this).val().replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
        $(this).val(value);
    });

    // Contador de caracteres para descripción
    $('#description').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        
        let counterText = `${currentLength}/${maxLength} caracteres`;
        if (remaining < 50) {
            counterText = `<span class="text-warning">${counterText}</span>`;
        }
        if (remaining < 0) {
            counterText = `<span class="text-danger">${counterText}</span>`;
        }
        
        $(this).next('.form-text').html(counterText);
    });

    // Validación inicial de campos pre-llenados
    $('input[required], textarea').each(function() {
        const fieldName = $(this).attr('name');
        const value = $(this).val();
        
        if (value && validationRules[fieldName]) {
            validateField(fieldName, value);
        }
    });

    // Actualizar estado inicial
    updateSubmitButton();
});
</script>

<style>
.form-control.is-valid {
    border-color: #28a745;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback, .valid-feedback {
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.btn:disabled {
    cursor: not-allowed;
}
</style>