<div class="container-fluid px-0">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('name', 'Nombre:') !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre de la zona', 'required']) !!}
                <span class="text-danger error-text name_error"></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('department_id', 'Departamento:') !!}
                {!! Form::select('department_id', $departments, null, ['class' => 'form-control select2', 'placeholder' => 'Seleccione un departamento', 'required']) !!}
                <span class="text-danger error-text department_id_error"></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('average_waste', 'Residuos promedio (Tb):') !!}
                <div class="input-group">
                    {!! Form::number('average_waste', null, ['class' => 'form-control', 'placeholder' => 'Ingrese residuos promedio', 'step' => '0.01']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text">Tb</span>
                    </div>
                </div>
                <span class="text-danger error-text average_waste_error"></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('status', 'Estado:') !!}
                <div class="custom-control custom-switch mt-2">
                    {!! Form::checkbox('status_switch', 'A', true, ['class' => 'custom-control-input', 'id' => 'statusSwitch']) !!}
                    {!! Form::label('statusSwitch', 'Activo', ['class' => 'custom-control-label status-label']) !!}
                    <input type="hidden" name="status" id="statusHidden" value="A">
                </div>
                <span class="text-danger error-text status_error"></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('description', 'Descripción:') !!}
                {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción de la zona', 'rows' => 2, 'style' => 'resize: none;']) !!}
                <span class="text-danger error-text description_error"></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group mb-1">
                <label for="map">{{ isset($zone) ? 'Modifique' : 'Dibuje' }} la zona en el mapa:</label>
                <div id="map" style="height: 350px; border: 1px solid #ddd; border-radius: 4px;"></div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-1">
                <div>
                    <button type="button" class="btn btn-secondary btn-sm mr-2" id="clearPolygon">
                        <i class="fas fa-trash-alt"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" id="removeLastPoint">
                        <i class="fas fa-undo"></i> Eliminar último punto
                    </button>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-info-circle"></i> Haga clic para agregar puntos y arrastre para ajustar la forma
                </div>
            </div>
            <span class="text-danger error-text coords_error"></span>
        </div>
    </div>

    <div id="coordinates-container"></div>
</div>

<script>
    var polygonCoords = [];
    var polygon = null;
    var markers = [];
    var map;

    $(document).ready(function() {
        if($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalZone')
            });
        }
        
        $('#statusSwitch').change(function() {
            if ($(this).is(':checked')) {
                $('.status-label').text('Activo');
                $('#statusHidden').val('A');
            } else {
                $('.status-label').text('Inactivo');
                $('#statusHidden').val('I');
            }
        });
        
        @if(isset($zone) && $zone->status == 'I')
            $('#statusSwitch').prop('checked', false);
            $('.status-label').text('Inactivo');
            $('#statusHidden').val('I');
        @endif
        
        try {
            map = L.map('map').setView([-6.7711, -79.8430], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            @if(isset($zone) && $zone->coords()->exists())
                @foreach($zone->coords()->orderBy('coord_index')->get() as $coord)
                    polygonCoords.push({
                        lat: {{ $coord->latitude }},
                        lng: {{ $coord->longitude }}
                    });
                @endforeach

                if (polygonCoords.length >= 3) {
                    polygon = L.polygon(polygonCoords, {
                        color: 'blue'
                    }).addTo(map);

                    polygonCoords.forEach(function(coord, index) {
                        var marker = L.marker([coord.lat, coord.lng], {
                            draggable: true,
                            icon: L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div style="background-color: #3388ff; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white;"></div>`,
                                iconSize: [15, 15],
                                iconAnchor: [7, 7]
                            })
                        }).addTo(map);
                        
                        marker.coordIndex = index;
                        
                        marker.on('dragend', function(event) {
                            var marker = event.target;
                            var position = marker.getLatLng();
                            
                            polygonCoords[marker.coordIndex] = {
                                lat: position.lat,
                                lng: position.lng
                            };
                            
                            updatePolygon();
                        });
                        
                        markers.push(marker);
                    });

                    setTimeout(function() {
                        map.fitBounds(polygon.getBounds(), {
                            padding: [50, 50],
                            maxZoom: 18
                        });
                    }, 100);

                    updateHiddenFields();
                }
            @endif

            function updatePolygon() {
                if (polygon) {
                    map.removeLayer(polygon);
                }

                if (polygonCoords.length >= 3) {
                    polygon = L.polygon(polygonCoords, {
                        color: 'blue'
                    }).addTo(map);

                    map.fitBounds(polygon.getBounds(), {
                        padding: [50, 50],
                        maxZoom: 18
                    });
                }

                updateHiddenFields();
            }

            function updateHiddenFields() {
                $('#coordinates-container').empty();

                polygonCoords.forEach(function(coord, index) {
                    $('#coordinates-container').append(
                        `<input type="hidden" name="coords[${index}][latitude]" value="${coord.lat}">
                        <input type="hidden" name="coords[${index}][longitude]" value="${coord.lng}">`
                    );
                });
            }

            map.on('click', function(e) {
                var newLatLng = e.latlng;
                polygonCoords.push(newLatLng);
                
                var marker = L.marker(newLatLng, {
                    draggable: true,
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background-color: #3388ff; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white;"></div>`,
                        iconSize: [15, 15],
                        iconAnchor: [7, 7]
                    })
                }).addTo(map);
                
                marker.coordIndex = polygonCoords.length - 1;
                
                marker.on('dragend', function(event) {
                    var marker = event.target;
                    var position = marker.getLatLng();
                    
                    polygonCoords[marker.coordIndex] = {
                        lat: position.lat,
                        lng: position.lng
                    };
                    
                    updatePolygon();
                });
                
                markers.push(marker);
                updatePolygon();
            });

            $('#clearPolygon').click(function() {
                polygonCoords = [];

                markers.forEach(function(marker) {
                    map.removeLayer(marker);
                });
                markers = [];

                if (polygon) {
                    map.removeLayer(polygon);
                    polygon = null;
                }

                $('#coordinates-container').empty();
            });
            
            $('#removeLastPoint').click(function() {
                if (markers.length > 0) {
                    var lastMarker = markers.pop();
                    map.removeLayer(lastMarker);
                    
                    polygonCoords.pop();
                    
                    if (polygonCoords.length < 3) {
                        if (polygon) {
                            map.removeLayer(polygon);
                            polygon = null;
                        }
                    } else {
                        updatePolygon();
                    }
                    
                    updateHiddenFields();
                }
            });

            $('#modalZone').on('shown.bs.modal', function() {
                map.invalidateSize();

                if (polygon) {
                    setTimeout(function() {
                        map.fitBounds(polygon.getBounds(), {
                            padding: [50, 50],
                            maxZoom: 18
                        });
                    }, 100);
                }
            });
            
            $('<style>.custom-div-icon { background: transparent; border: none; } .leaflet-marker-icon { cursor: grab; }</style>').appendTo('head');
            
        } catch (error) {
            console.error("Error al inicializar el mapa:", error);
        }
    });
</script>