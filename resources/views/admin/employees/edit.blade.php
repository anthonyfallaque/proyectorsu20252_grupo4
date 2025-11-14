<form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" id="employeeEditForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="dni">DNI <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control" 
                       id="dni" 
                       name="dni" 
                       value="{{ old('dni', $employee->dni) }}"
                       placeholder="12345678" 
                       maxlength="8"
                       required>
                <div class="invalid-feedback" id="dni-error"></div>
                <small class="form-text text-muted">8 dígitos únicos</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="type_id">Tipo de Empleado <span class="text-danger">*</span></label>
                <select class="form-control" id="type_id" name="type_id" required>
                    <option value="">Seleccione un tipo</option>
                    @foreach($employeeTypes as $type)
                        <option value="{{ $type->id }}" {{ $employee->type_id == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="type_id-error"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="names">Nombres <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control" 
                       id="names" 
                       name="names" 
                       value="{{ old('names', $employee->names) }}"
                       placeholder="Juan Carlos" 
                       maxlength="100"
                       required>
                <div class="invalid-feedback" id="names-error"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="lastnames">Apellidos <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control" 
                       id="lastnames" 
                       name="lastnames" 
                       value="{{ old('lastnames', $employee->lastnames) }}"
                       placeholder="Pérez García" 
                       maxlength="200"
                       required>
                <div class="invalid-feedback" id="lastnames-error"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="birthday">Fecha de Nacimiento <span class="text-danger">*</span></label>
                <input type="date" 
                       class="form-control" 
                       id="birthday" 
                       name="birthday"
                       value="{{ old('birthday', $employee->birthday ? $employee->birthday->format('Y-m-d') : '') }}"
                       required>
                <div class="invalid-feedback" id="birthday-error"></div>
                <small class="form-text text-muted">Mayor de 18 años</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group" id="license-group" style="{{ strpos(strtolower($employee->employeeType->name ?? ''), 'conductor') !== false ? '' : 'display: none;' }}">
                <label for="license">Licencia de Conducir <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control" 
                       id="license" 
                       name="license" 
                       value="{{ old('license', $employee->license) }}"
                       placeholder="A12345678" 
                       maxlength="20"
                       {{ strpos(strtolower($employee->employeeType->name ?? ''), 'conductor') !== false ? 'required' : '' }}>
                <div class="invalid-feedback" id="license-error"></div>
                <small class="form-text text-muted">Obligatorio para conductores - Formato: A12345678</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" 
                       class="form-control" 
                       id="phone" 
                       name="phone" 
                       value="{{ old('phone', $employee->phone) }}"
                       placeholder="987654321" 
                       maxlength="20">
                <div class="invalid-feedback" id="phone-error"></div>
                <small class="form-text text-muted">Opcional - Formato: 987654321</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', $employee->email) }}"
                       placeholder="empleado@ejemplo.com" 
                       maxlength="100">
                <div class="invalid-feedback" id="email-error"></div>
                <small class="form-text text-muted">Opcional</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="photo">Fotografía</label>
                <input type="file" 
                       class="form-control-file" 
                       id="photo" 
                       name="photo" 
                       accept="image/jpeg,image/png,image/jpg">
                <div class="invalid-feedback" id="photo-error"></div>
                
                <div class="mt-2">
                    <small class="form-text text-muted">
                        @if($employee->photo)
                            <i class="fas fa-info-circle"></i> El empleado ya tiene una foto. Selecciona una nueva imagen solo si deseas cambiarla.
                        @else
                            <i class="fas fa-info-circle"></i> Selecciona una imagen si deseas agregar una foto al empleado.
                        @endif
                    </small>
                </div>
                
                <!-- Preview de nueva imagen -->
                <div class="mt-3 text-center d-none" id="photo-preview">
                    <p class="mb-2"><strong class="text-success">Nueva imagen seleccionada:</strong></p>
                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 120px; max-height: 120px; object-fit: cover;">
                </div>
                
                <small class="form-text text-muted">Formatos: JPG, PNG. Máximo 2MB</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Dejar vacío para mantener actual"
                       minlength="8">
                <div class="invalid-feedback" id="password-error"></div>
                <small class="form-text text-muted">Solo llenar si desea cambiar contraseña</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="form-check">
                    <input type="hidden" name="status" value="0">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="status" 
                           name="status" 
                           value="1" 
                           {{ $employee->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">
                        <strong>Empleado activo</strong>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="address">Dirección <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control" 
               id="address" 
               name="address" 
               value="{{ old('address', $employee->address) }}"
               placeholder="Av. Principal 123, Distrito, Ciudad" 
               maxlength="200"
               required>
        <div class="invalid-feedback" id="address-error"></div>
        <small class="form-text text-muted">Dirección completa (mínimo 10 caracteres)</small>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Nota:</strong> Los campos marcados con (*) son obligatorios. La contraseña solo se cambia si se proporciona una nueva.
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="submit-btn">Actualizar</button>
    </div>
</form>

<script>
$(document).ready(function() {
    let validationErrors = {};
    const currentEmployeeId = {{ $employee->id }};

    // Configuración de validaciones
    const validationRules = {
        dni: {
            required: true,
            pattern: /^\d{8}$/,
            unique: true,
            message: 'El DNI debe tener exactamente 8 dígitos'
        },
        names: {
            required: true,
            pattern: /^[a-zA-ZÀ-ÿ\s]+$/,
            minLength: 2,
            maxLength: 100,
            message: 'Los nombres solo pueden contener letras y espacios'
        },
        lastnames: {
            required: true,
            pattern: /^[a-zA-ZÀ-ÿ\s]+$/,
            minLength: 2,
            maxLength: 200,
            message: 'Los apellidos solo pueden contener letras y espacios'
        },
        birthday: {
            required: true,
            custom: 'validateAge',
            message: 'Debe ser mayor de 18 años'
        },
        license: {
            required: {{ strpos(strtolower($employee->employeeType->name ?? ''), 'conductor') !== false ? 'true' : 'false' }},
            pattern: /^[A-Z]\d{8}$/,
            unique: true,
            message: 'La licencia debe tener formato A12345678'
        },
        phone: {
            required: false,
            pattern: /^(\+?51)?9\d{8}$/,
            message: 'Debe ser un teléfono peruano válido (987654321)'
        },
        email: {
            required: false,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            unique: true,
            message: 'Formato de email inválido'
        },
        type_id: {
            required: true,
            message: 'Debe seleccionar un tipo de empleado'
        },
        address: {
            required: true,
            minLength: 10,
            maxLength: 200,
            message: 'La dirección debe tener entre 10 y 200 caracteres'
        },
        password: {
            required: false, // No requerido en edición
            minLength: 8,
            message: 'La contraseña debe tener al menos 8 caracteres'
        },
        photo: {
            required: false,
            custom: 'validatePhoto',
            message: 'Archivo de imagen inválido'
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

        // Validación personalizada
        if (rule.custom) {
            return window[rule.custom](fieldName, value);
        }

        // Verificar unicidad para edición
        if (rule.unique && value) {
            checkUniqueEdit(fieldName, value);
            return true;
        }

        setFieldValid(fieldName);
        return true;
    }

    // Validación de edad
    window.validateAge = function(fieldName, value) {
        if (!value) return false;
        
        const birthDate = new Date(value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        if (age < 18) {
            setFieldError(fieldName, 'Debe ser mayor de 18 años');
            return false;
        }
        
        if (age > 65) {
            setFieldError(fieldName, 'Debe ser menor de 65 años');
            return false;
        }
        
        setFieldValid(fieldName);
        return true;
    };

    // Validación de foto
    window.validatePhoto = function(fieldName, value) {
        const fileInput = document.getElementById('photo');
        const file = fileInput.files[0];
        
        if (!file) {
            setFieldValid(fieldName);
            return true;
        }
        
        // Validar tipo de archivo
        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            setFieldError(fieldName, 'Solo se permiten archivos JPG, PNG');
            return false;
        }
        
        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
            setFieldError(fieldName, 'La imagen no puede ser mayor a 2MB');
            return false;
        }
        
        setFieldValid(fieldName);
        return true;
    };

    // Verificar unicidad para edición
    function checkUniqueEdit(field, value) {
        if (!value) return;
        
        $.ajax({
            url: '/admin/employees/check-unique',
            method: 'POST',
            data: {
                field: field,
                value: value,
                employee_id: currentEmployeeId, // Excluir empleado actual
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.unique) {
                    setFieldValid(field);
                } else {
                    setFieldError(field, `Ya existe un empleado con este ${field}`);
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
        let requiredFields = ['dni', 'names', 'lastnames', 'birthday', 'type_id', 'address'];
        
        // Agregar licencia si es conductor
        const typeName = $('#type_id option:selected').text().toLowerCase();
        if (typeName.includes('conductor')) {
            requiredFields.push('license');
        }
        
        const allRequiredFilled = requiredFields.every(field => {
            const value = $(`#${field}`).val();
            return value && value.trim() !== '';
        });

        const isFormValid = !hasErrors && allRequiredFilled;
        $('#submit-btn').prop('disabled', !isFormValid);
        
        if (isFormValid) {
            $('#submit-btn').removeClass('btn-secondary').addClass('btn-primary');
        } else {
            $('#submit-btn').removeClass('btn-primary').addClass('btn-secondary');
        }
    }

    // Event listeners
    $('input, select').on('input change blur', function() {
        const fieldName = $(this).attr('name');
        const value = $(this).val();
        
        if (validationRules[fieldName]) {
            validateField(fieldName, value);
        }
    });

    // Mostrar/ocultar licencia según tipo de empleado
    $('#type_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const typeName = selectedOption.text().toLowerCase();
        const licenseGroup = $('#license-group');
        const licenseInput = $('#license');
        
        if (typeName.includes('conductor')) {
            // Mostrar campo de licencia y hacerlo obligatorio
            licenseGroup.show();
            licenseInput.attr('required', true);
            validationRules.license.required = true;
            
            // Validar si ya tiene valor
            if (licenseInput.val()) {
                validateField('license', licenseInput.val());
            }
        } else {
            // Ocultar campo de licencia y quitar obligatoriedad
            licenseGroup.hide();
            licenseInput.removeAttr('required');
            validationRules.license.required = false;
            
            // Limpiar validación
            setFieldValid('license');
        }
        
        updateSubmitButton();
    });

    // Formatear campos automáticamente
    $('#dni').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 8) value = value.slice(0, 8);
        $(this).val(value);
    });

    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 9) value = value.slice(0, 9);
        $(this).val(value);
    });

    $('#names, #lastnames').on('input', function() {
        let value = $(this).val().replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
        value = value.replace(/\s+/g, ' ');
        $(this).val(value);
    });

    $('#license').on('input', function() {
        let value = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (value.length > 9) value = value.slice(0, 9);
        $(this).val(value);
    });

    // Preview de imagen
    $('#photo').on('change', function() {
        const file = this.files[0];
        const preview = $('#photo-preview');
        const img = preview.find('img');
        
        if (file) {
            // Validar archivo
            if (validateField('photo', file)) {
                // Mostrar preview de la nueva imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.attr('src', e.target.result);
                    preview.removeClass('d-none');
                };
                reader.readAsDataURL(file);
            }
        } else {
            // Ocultar preview si no hay archivo
            preview.addClass('d-none');
        }
    });

    // Validación con debounce para evitar muchas peticiones AJAX
    let timeoutId;
    $('#dni, #email, #license').on('input', function() {
        const fieldName = $(this).attr('name');
        const value = $(this).val();
        
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            if (value && validationRules[fieldName]) {
                validateField(fieldName, value);
            }
        }, 500);
    });

    // Validación inicial de campos pre-llenados
    $('input[required], select[required]').each(function() {
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

.invalid-feedback {
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.btn:disabled {
    cursor: not-allowed;
}

.alert-info {
    border-left: 4px solid #17a2b8;
}

.row .col-md-6 {
    padding-right: 8px;
    padding-left: 8px;
}

.img-thumbnail {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    border: 2px solid #e9ecef;
}

#current-photo img, #photo-preview img {
    transition: transform 0.2s ease;
}

#current-photo img:hover, #photo-preview img:hover {
    transform: scale(1.05);
}

#no-photo .border {
    border-color: #dee2e6 !important;
    transition: background-color 0.2s ease;
}

#no-photo .border:hover {
    background-color: #e9ecef !important;
}
</style>