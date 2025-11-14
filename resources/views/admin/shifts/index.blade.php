@extends('adminlte::page')

@section('title', 'Turnos')

@section('content')
<div class="p-2"></div>

<!-- Modal -->
<div class="modal fade" id="modalShift" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
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
        <h3 class="card-title">Lista de Turnos</h3>
        <div class="card-tools">
            <button id="btnNewShift" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Nuevo Turno</button>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped" id="datatableShifts" style="width:100%">
            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>HORARIO</th>
                    <th>DESCRIPCIÓN</th>
                    <th>CREADO</th>
                    <th>ACTUALIZADO</th>
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

@section('css')
<!-- FontAwesome para íconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#datatableShifts').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            },
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.shifts.index') }}",
            columns: [{
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'schedule',
                    name: 'schedule',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'description_badge',
                    name: 'description_badge',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [3, 'desc']
            ]
        });

        $('#btnNewShift').click(function() {
            $.ajax({
                url: "{{ route('admin.shifts.create') }}",
                type: "GET",
                success: function(response) {
                    $('#ModalLongTitle').text('Nuevo Turno');
                    $('#modalShift .modal-body').html(response);
                    $('#modalShift').modal('show');

                    $('#modalShift form').off('submit').on('submit', function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var formData = new FormData(this);
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $('#modalShift').modal('hide');
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
                                    text: res.message || 'Ocurrió un error',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    });
                }
            });
        });

        // Editar turno - abrir modal
        $(document).on('click', '.btnEditar', function() {
            var shiftId = $(this).attr('id');
            $.ajax({
                url: "{{ route('admin.shifts.edit', ':id') }}".replace(':id', shiftId),
                type: "GET",
                success: function(response) {
                    $('#ModalLongTitle').text('Editar Turno');
                    $('#modalShift .modal-body').html(response);
                    $('#modalShift').modal('show');

                    // Enviar formulario AJAX para actualizar
                    $('#modalShift form').off('submit').on('submit', function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var formData = new FormData(this);
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $('#modalShift').modal('hide');
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
                                    text: res.message || 'Ocurrió un error',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    });
                }
            });
        });

        // Eliminar turno con confirmación
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
    });
</script>
@stop