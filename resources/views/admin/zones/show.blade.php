
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre:</label>
            <p>{{ $zone->name }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Fecha de creación:</label>
            <p>{{ $zone->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Descripción:</label>
    <p>{{ $zone->description ?: 'Sin descripción' }}</p>
</div>

<div class="form-group">
    <label>Mapa de la zona:</label>
    <div id="map" style="height: 400px;"></div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
</div>   

<script>
    $(document).ready(function() {
        var polygonCoords = [];
        
        @foreach($zone->coords()->orderBy('coord_index')->get() as $coord)
            polygonCoords.push([
                {{ $coord->latitude }},
                {{ $coord->longitude }}
            ]);
        @endforeach
        
        var map = L.map('map');
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        if (polygonCoords.length >= 3) {
            var polygon = L.polygon(polygonCoords, {color: 'blue'}).addTo(map);
            
            map.fitBounds(polygon.getBounds(), {
                padding: [50, 50], 
                maxZoom: 15        
            });
        } else {
            map.setView([-6.7711, -79.8430], 13);
        }
        
        $('#modalZone').on('shown.bs.modal', function () {
            map.invalidateSize();
            
            if (polygon) {
                map.fitBounds(polygon.getBounds());
            }
        });
    });
</script>