{!! Form::open(['route' => 'admin.vehicles.storeImages', 'files' => true, 'method' => 'POST']) !!}
{!! Form::hidden('vehicle_id', $vehicle->id) !!}

<div class="mb-3 row">
    <div class="col-md-12">
        {!! Form::file('image', ['id' => 'logo', 'accept' => 'image/*', 'class' => 'd-none']) !!}
        <div id="imageButton" class="p-3 mb-3 text-center border rounded-lg shadow-sm border-secondary"
            style="cursor: pointer; background-color: #f8f9fa;">
            <img src="{{ asset('storage/brand_logo/no_image.png') }}" alt="Logo" class="mb-2 img-thumbnail"
                style="height: 180px; object-fit: contain;">
            <p class="mb-0 text-secondary"><i class="fas fa-upload"></i> Haz clic para seleccionar una imagen</p>
        </div>

        <div class="mb-3 form-check">
            {!! Form::checkbox('profile', 1, true, ['class' => 'form-check-input', 'id' => 'profile']) !!}
            <label class="form-check-label" for="profile">Establecer como imagen principal</label>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>

        </div>
    </div>
</div>
{!! Form::close() !!}


<hr>

<h5 class="mt-4">Imágenes del vehículo</h5>
<div class="row">
    @forelse ($imagesVehicle as $image)
        <div class="mb-4 col-md-3">
            <div class="border-0 shadow-sm card h-100">
                <img src="{{ asset($image->image) }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                <div class="text-center card-body">
                    @if ($image->profile)
                        <span class="mb-2 badge badge-success">Imagen principal</span>
                    @else
                        <form method="POST" action="{{ route('admin.vehicles.setProfile', $image->id) }}"
                            class="mb-2 form-set-profile">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info">Establecer</button>
                        </form>
                    @endif


                    <form method="POST" action="{{ route('admin.vehicles.deleteImage', $image->id) }}"
                        class="form-delete-image">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center col-12 text-muted">No hay imágenes registradas.</div>
    @endforelse
</div>

<script>
    $('#logo').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imageButton img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    $('#imageButton').click(function() {
        $('#logo').click();
    });
</script>

<script>
    // Confirmar "Establecer como principal"
    $(document).on('submit', '.form-set-profile', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '¿Establecer como imagen principal?',
            text: "Esta acción reemplazará la imagen principal actual.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, establecer',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Confirmar "Eliminar imagen"
    $(document).on('submit', '.form-delete-image', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '¿Eliminar imagen?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
