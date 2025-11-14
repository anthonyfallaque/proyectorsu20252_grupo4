@extends('adminlte::page')

@section('title', 'Grupo de Personal')

@section('css')
<style>
    /* Modificar la altura del contenedor de selección */
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px) !important; /* Asegúrate de usar !important si es necesario */
        padding: 6px 12px;
    }

    /* Cambiar el color de fondo del dropdown */
    .select2-container--default .select2-dropdown {
        background-color: #f8f9fa !important;  /* Fondo claro */
        border-radius: 4px;
    }

    /* Cambiar el color de texto del ítem seleccionado */
    .select2-container--default .select2-selection__rendered {
        color: #333 !important;  /* Cambiar el color del texto */
    }
</style>

@stop

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade" id="modalEmployeeGroup" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalLongTitle"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          {{-- Contenido del formulario cargado por AJAX --}}
        </div>
      </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
              <i class="fas fa-users"></i> Grupo de Personal
        </h3>
        <div class="card-tools">
            <button id="btnNewEmployeeGroup" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Grupo de Personal</button> 
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped" id="datatableEmployeeGroups" style="width:100%">
            <thead>
                <tr>
                    <th>GRUPO</th>
                    <th>ZONA</th>
                    <th>TURNO</th>
                    <th>DÍAS</th>
                    <th>VEHICULO</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                {{-- Si usas serverSide, queda vacío --}}
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#datatableEmployeeGroups').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        },
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.employeegroups.index') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'zone', name: 'zone' },
            { data: 'shift', name: 'shift' },
            { data: 'days', name: 'days' },
            { data: 'vehicle', name: 'vehicle' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    // Nuevo empleado - abrir modal
    $('#btnNewEmployeeGroup').click(function() {
        $.ajax({
            url: "{{ route('admin.employeegroups.create') }}",
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Nuevo Grupo de Personal');
                $('#modalEmployeeGroup .modal-body').html(response);
                $('#modalEmployeeGroup').modal('show');
                inicializarFormularioEmployeeGroup();

                // Enviar formulario AJAX para crear
                $('#modalEmployeeGroup form').submit(function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var formData = new FormData(this);

                    const data = {
                        _token: '{{ csrf_token() }}',
                        name: formData.get('group_name'),
                        zone_id: parseInt(formData.get('zone_id')),
                        vehicle_id: parseInt(formData.get('vehicle_id')),
                        shift_id: parseInt(formData.get('shift_id')),
                        days:[...formData.getAll('days[]')],
                        driver_id: parseInt(formData.get('driver_id'))|| null,
                        helpers:[...formData.getAll('helpers[]').map(Number).filter(n => n > 0)]
                    }
                    console.log(data)

                    $.ajax({
                        url: "{{ route('admin.employeegroups.store') }}",
                        type: "POST",
                        data: data,
                        success: function(response) {
                            $('#modalEmployeeGroup').modal('hide');
                            table.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        },
                        error: function(xhr) {
                            let res = xhr.responseJSON;
                            let errors = res.errors || {};
                            let errorMessage = res.message || 'Ocurrió un error';
                            
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('\n');
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: errorMessage,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                    
                });
            }
        });
    });

    // Editar empleado - abrir modal
    $(document).on('click', '.btnEditar', function() {
        var employeeGroupId = $(this).attr('id');
        $.ajax({
            url: '{{ route('admin.employeegroups.edit', 'id') }}'.replace('id', employeeGroupId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Editar Grupo de Personal');
                $('#modalEmployeeGroup .modal-body').html(response);
                $('#modalEmployeeGroup').modal('show');

                inicializarFormularioEmployeeGroup();

                // Enviar formulario AJAX para actualizar
                $('#modalEmployeeGroup form').submit(function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var formData = new FormData(this);

                    const data = {
                        _token: '{{ csrf_token() }}',
                        name: formData.get('group_name'),
                        zone_id: parseInt(formData.get('zone_id')),
                        vehicle_id: parseInt(formData.get('vehicle_id')),
                        shift_id: parseInt(formData.get('shift_id')),
                        days:[...formData.getAll('days[]')],
                        driver_id: parseInt(formData.get('driver_id'))|| null,
                        helpers:[...formData.getAll('helpers[]').map(Number).filter(n => n > 0)]
                    }
                    console.log(form.attr('method'))
                    $.ajax({
                        url: form.attr('action'),
                        type: 'PUT',
                        data: data,
                        success: function(response) {
                            $('#modalEmployeeGroup').modal('hide');
                            table.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        },
                        error: function(xhr) {
                            let res = xhr.responseJSON;
                            let errors = res.errors || {};
                            let errorMessage = res.message || 'Ocurrió un error';
                            
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('\n');
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: errorMessage,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                });
            }
        });
    });

    // Eliminar empleado con confirmación
    $(document).on('submit', '.delete', function(e) {
        e.preventDefault();
        let form = $(this);

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Este cambio no se puede deshacer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: res?.message || 'Ocurrió un error',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.btnVer', function() {
        var employeeGroupId = $(this).attr('id');
        $.ajax({
            url: '{{ route('admin.employeegroups.show', 'id') }}'.replace('id', employeeGroupId),
            type: "GET",
            success: function(response) {
                $('#ModalLongTitle').text('Ver Grupo de Personal');
                $('#modalEmployeeGroup .modal-body').html(response);
                $('#modalEmployeeGroup').modal('show');
            }
        });
    });


        
    function initializeDynamicSelects() {
         $('select[name="driver_id').select2({
            placeholder: 'Seleccione un conductor',
            dropdownParent: $('#modalEmployeeGroup'),
        });

        $('select[name="helpers[]"]').select2({
            placeholder: 'Seleccione un ayudante',
            dropdownParent: $('#modalEmployeeGroup'),
        });
    }

    $('#ayudantes-container').on('change', 'select', function() {
        initializeDynamicSelects();  // Vuelve a inicializar Select2 si es necesario
    });



    function inicializarFormularioEmployeeGroup(){
        const vehicleSelect = document.getElementById('vehicle_id');
        const ayudantesContainer = document.getElementById('ayudantes-container');
        const dataExtra = document.getElementById('dataExtra')
        if (!vehicleSelect) return;
        const ayudantesData = window.ayudantesData || [];


        vehicleSelect.addEventListener('change', function () {

            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            if (selectedOption.value > 0) {
                dataExtra.classList.remove('d-none');
            }else{
                dataExtra.classList.add('d-none');
            }

            const capacidad = parseInt(selectedOption.getAttribute('data-capacidad')) || 1;
            ayudantesContainer.innerHTML = ''; // limpiar antes de crear nuevos campos

            const numAyudantes = capacidad - 1;

            for (let i = 1; i <= numAyudantes; i++) {
                const div = document.createElement('div');
                div.classList.add('form-group');
                div.classList.add('col-md-6');

                const label = document.createElement('label');
                label.textContent = `Ayudante ${i}`;

                const select = document.createElement('select');
                select.name = `helpers[]`;
                select.classList.add('form-control');

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Seleccione un ayudante';
                select.appendChild(defaultOption);
                select.addEventListener('change', function() {
                    actualizarOpcionesAyudantes();
                });

            ayudantesData.forEach(function(ayudante) {
                const option = document.createElement('option');
                option.value = ayudante.id;
                option.textContent = ayudante.name;
                select.appendChild(option);
            });

            div.appendChild(label);
            div.appendChild(select);
            ayudantesContainer.appendChild(div);
            initializeDynamicSelects();
        }
    });

    if (vehicleSelect.value > 0) {
        dataExtra.classList.remove('d-none');
    }

    }

    function actualizarOpcionesAyudantes() {
        const selects = document.querySelectorAll('select[name="helpers[]"]');
        const selectedValues = [];

        // Primero recopilar los valores seleccionados
        selects.forEach(function(select) {
            const selectedValue = select.value;
            if (selectedValue) {
                selectedValues.push(selectedValue);
            }
        });

        selects.forEach(function(select) {
            const options = select.options;
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                if (selectedValues.includes(option.value) && option.value !== select.value) {
                    option.disabled = true;  // Deshabilitar solo si está seleccionado en otro select
                } else {
                    option.disabled = false;
                }
            }
        });
    }

});
</script>
@stop