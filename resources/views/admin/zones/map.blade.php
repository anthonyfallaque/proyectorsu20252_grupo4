
@extends('adminlte::page')

@section('title', 'Mapa de Zonas')

@section('content_header')
    <h1>Mapa de Zonas</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Visualización de zonas activas</h3>
        <div class="card-tools">
            <a href="{{ route('admin.zones.index') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> Volver al listado
            </a>
        </div>
    </div>
    <div class="card-body">
        <div id="map" style="height: 600px;"></div>
    </div>
</div>

<div class="modal fade" id="zoneDetailsModal" tabindex="-1" role="dialog" aria-labelledby="zoneDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="zoneDetailsModalLabel">Detalles de la Zona</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3" id="modal-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>
                <div id="zone-details-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-map-marker-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nombre</span>
                                    <span class="info-box-number" id="zone-name"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-map"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Puntos</span>
                                    <span class="info-box-number" id="zone-points"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Departamento</span>
                                    <span class="info-box-number" id="zone-department"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-trash"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Residuos promedio</span>
                                    <span class="info-box-number" id="zone-waste"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Descripción</h3>
                        </div>
                        <div class="card-body">
                            <p id="zone-description"></p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Coordenadas</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Latitud</th>
                                            <th>Longitud</th>
                                        </tr>
                                    </thead>
                                    <tbody id="zone-coords-table">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .zone-popup .zone-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .zone-popup .zone-description {
            margin-bottom: 8px;
        }
        .zone-popup .zone-details {
            margin-top: 8px;
        }
    </style>
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            var map = L.map('map').setView([-6.7711, -79.8430], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            function getRandomColor() {
                var letters = '0123456789ABCDEF';
                var color = '#';
                for (var i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }
            
            var allPolygons = [];
            
            @foreach($zones as $zone)
                @if($zone->status == 'A' && $zone->coords->count() >= 3)
                    var zoneCoords = [];
                    @foreach($zone->coords()->orderBy('coord_index')->get() as $coord)
                        zoneCoords.push([{{ $coord->latitude }}, {{ $coord->longitude }}]);
                    @endforeach
                    
                    var color = getRandomColor();
                    var polygon = L.polygon(zoneCoords, {
                        color: color,
                        fillColor: color,
                        fillOpacity: 0.4,
                        weight: 2
                    }).addTo(map);
                    
                    polygon.bindPopup(`
                        <div class="zone-popup">
                            <div class="zone-name">{{ $zone->name }}</div>
                            @if($zone->department)
                                <div class="zone-department"><strong>Departamento:</strong> {{ $zone->department->name }}</div>
                            @endif
                            @if($zone->average_waste)
                                <div class="zone-waste"><strong>Residuos promedio:</strong> {{ $zone->average_waste }} Tb</div>
                            @endif
                            <div class="zone-description">{{ $zone->description ?: 'Sin descripción' }}</div>
                            <div class="zone-coords">{{ $zone->coords->count() }} puntos</div>
                            <div class="zone-details">
                                <a href="#" 
                                   class="btn btn-sm btn-info" 
                                   onclick="event.preventDefault(); showZoneDetails({{ $zone->id }});">
                                   <i class="fas fa-eye"></i> Ver detalles
                                </a>
                            </div>
                        </div>
                    `);
                    
                    allPolygons.push(polygon);
                @endif
            @endforeach
            
            if (allPolygons.length > 0) {
                var group = new L.featureGroup(allPolygons);
                map.fitBounds(group.getBounds(), {
                    padding: [50, 50]
                });
            }
            
            window.showZoneDetails = function(zoneId) {
                $('#modal-loading').show();
                $('#zone-details-content').hide();
                $('#zoneDetailsModal').modal('show');
                
                $.ajax({
                    url: "{{ url('admin/zones') }}/" + zoneId + "/ajax",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#zone-name').text(response.name);
                        $('#zone-department').text(response.department ? response.department.name : 'N/A');
                        $('#zone-waste').text(response.average_waste ? response.average_waste + ' Tb' : 'No especificado');
                        $('#zone-description').text(response.description || 'Sin descripción');
                        $('#zone-points').text(response.coords.length);
                        
                        var coordsHtml = '';
                        response.coords.forEach(function(coord, index) {
                            coordsHtml += `<tr>
                                <td>${index + 1}</td>
                                <td>${coord.latitude}</td>
                                <td>${coord.longitude}</td>
                            </tr>`;
                        });
                        $('#zone-coords-table').html(coordsHtml);
                        
                        $('#modal-loading').hide();
                        $('#zone-details-content').show();
                    },
                    error: function() {
                        $('#zone-details-content').html('<div class="alert alert-danger">Error al cargar los datos de la zona</div>');
                        $('#modal-loading').hide();
                        $('#zone-details-content').show();
                    }
                });
            };
        });
    </script>
@stop