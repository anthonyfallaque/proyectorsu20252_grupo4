@extends('adminlte::page')

@section('title', 'Zonas')

@section('content_header')

@stop

@section('content')
<div class="p-2"></div>

<div class="modal fade" id="modalZone" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="ModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="ModalLongTitle">Zona</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
            </div>
            <div class="modal-footer py-1">
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Zonas</h3>
        <div class="card-tools">
            <a href="{{ route('admin.zones.map') }}" class="btn btn-info mr-2">
                <i class="fas fa-map-marked-alt"></i> Ver todas las zonas en el mapa
            </a>
            <button id="btnNewZone" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Nueva Zona
            </button>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped" id="datatable" style="width:100%">
            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>DEPARTAMENTO</th>
                    <th>ESTADO</th>
                    <th>COORDENADAS</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#datatable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            },
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.zones.index') }}",
            columns: [{
                    data: "name"
                },
                {
                    data: "department_name"
                },
                {
                    data: "status_badge",
                    orderable: false
                },
                {
                    data: "coordinates_count"
                },
                {
                    data: "action",
                    orderable: false,
                    searchable: false
                }
            ]
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#btnNewZone').click(function() {
            $.ajax({
                url: "{{ route('admin.zones.create') }}",
                type: "GET",
                success: function(response) {
                    $('#ModalLongTitle').text('Nueva Zona');
                    $('#modalZone .modal-body').html(response);
                    $('#modalZone').modal('show');

                    setTimeout(function() {
                        if (typeof map !== 'undefined') {
                            map.invalidateSize();
                        }
                    }, 300);

                    $(document).off('submit', '#zoneForm').on('submit', '#zoneForm', function(e) {
                        e.preventDefault();
                        $('.error-text').text('');

                        if (polygonCoords && polygonCoords.length < 3) {
                            $('.coords_error').text('Debe dibujar al menos 3 puntos en el mapa para formar un polígono válido.');
                            return false;
                        }

                        var form = $(this);
                        var formData = form.serialize();

                        $('#btnSaveZone').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            success: function(response) {
                                if (response.success) {
                                    $('#modalZone').modal('hide');
                                    table.ajax.reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Éxito!',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Aceptar'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '¡Error!',
                                        text: response.message || 'No se pudo guardar la zona',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Aceptar'
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.error("Error completo:", xhr.responseText);

                                if (xhr.status === 422) {
                                    var errors = xhr.responseJSON.errors;
                                    $.each(errors, function(key, value) {
                                        $('.' + key.replace(/\./g, '_') + '_error').text(value[0]);
                                    });

                                    Swal.fire({
                                        icon: 'error',
                                        title: '¡Error de validación!',
                                        text: 'Por favor corrija los errores señalados en el formulario.',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Aceptar'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: '¡Error!',
                                        text: 'No se pudo guardar la zona: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Error desconocido'),
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Aceptar'
                                    });
                                }
                            },
                            complete: function() {
                                $('#btnSaveZone').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                            }
                        });
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: 'No se pudo cargar el formulario.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });

        $(document).on('click', '.btnEditar', function() {
            var zoneId = $(this).attr('id');
            $.ajax({
                url: "{{ route('admin.zones.edit', 'id') }}".replace('id', zoneId),
                type: "GET",
                success: function(response) {
                    $('#ModalLongTitle').text('Editar Zona');
                    $('#modalZone .modal-body').html(response);
                    $('#modalZone').modal('show');

                    $('#modalZone form').submit(function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var formData = form.serialize();
                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: formData,
                            success: function(response) {
                                if (response.success) {
                                    $('#modalZone').modal('hide');
                                    table.ajax.reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Éxito!',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Aceptar'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var errors = xhr.responseJSON.errors;
                                var errorMessage = '';
                                $.each(errors, function(key, value) {
                                    errorMessage += value + '<br>';
                                });
                                Swal.fire({
                                    icon: 'error',
                                    title: '¡Error!',
                                    html: errorMessage,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar'
                                });
                            }
                        });
                    });
                }
            });
        });

        $(document).on('click', '.btnVer', function() {
            var zoneId = $(this).attr('id');
            $.ajax({
                url: "{{ route('admin.zones.show', 'id') }}".replace('id', zoneId),
                type: "GET",
                success: function(response) {
                    $('#ModalLongTitle').text('Detalles de la Zona');
                    $('#modalZone .modal-body').html(response);
                    $('#modalZone').modal('show');
                }
            });
        });


    });

    function confirmDelete(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Este cambio no se puede deshacer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/zones/' + id, 
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#datatable').DataTable().ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.message || 'Zona eliminada exitosamente.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: response.message || 'No se pudo eliminar la zona.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al eliminar:', xhr);

                        let errorMessage = 'Ha ocurrido un error al eliminar la zona.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
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
            }
        });
    }
</script>

@stop