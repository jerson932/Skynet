@csrf
<div class="grid gap-3 sm:grid-cols-2">
    <label class="block">Nombre
        <input name="name" value="{{ old('name', $client->name ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
        @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </label>
    <label class="block">Contacto
        <input name="contact_name" value="{{ old('contact_name', $client->contact_name ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <label class="block">Email
        <input name="email" type="email" value="{{ old('email', $client->email ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <label class="block">Teléfono
        <input name="phone" value="{{ old('phone', $client->phone ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <label class="block sm:col-span-2">Dirección
        <input id="address-input" name="address" value="{{ old('address', $client->address ?? '') }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="Escribe una dirección (opcional)">
    </label>
    <label class="block">Latitud
        <input id="lat-input" name="lat" type="number" step="0.000001" value="{{ old('lat', $client->lat ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <label class="block">Longitud
        <input id="lng-input" name="lng" type="number" step="0.000001" value="{{ old('lng', $client->lng ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
</div>

{{-- MAPA (Leaflet + OSM + Geocoder) --}}
<div class="mt-4">
    <label class="block font-medium mb-2">Ubicación en mapa</label>
    <div id="map"
     data-lat="{{ old('lat', $client->lat ?? '') }}"
     data-lng="{{ old('lng', $client->lng ?? '') }}"
     style="height: 340px; border-radius:.5rem; overflow:hidden; background:#f3f4f6;">
</div>

    <div class="mt-3 flex items-center gap-2">
        <button type="button" id="btn-geo" class="px-3 py-2 bg-gray-800 text-white rounded">Usar mi ubicación</button>
        <small class="text-gray-500">Busca con la lupa, haz clic en el mapa o arrastra el marcador.</small>
    </div>
</div>

<div class="mt-4 flex justify-end gap-2">
    <a href="{{ route('clients.web.index') }}" class="px-4 py-2 bg-gray-300 rounded">Cancelar</a>
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
</div>
@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
@endonce

<script>
(function () {
  function initLeafletClientForm() {
    if (window.__clientLeafletInited) return;
    window.__clientLeafletInited = true;

    const latInput  = document.getElementById('lat-input');
    const lngInput  = document.getElementById('lng-input');
    const addrInput = document.getElementById('address-input');
    const mapDiv    = document.getElementById('map');
    if (!latInput || !lngInput || !mapDiv) return;

    // Lee coords iniciales desde data-attributes (evita Blade dentro de JS)
    const dLat = parseFloat(mapDiv.dataset.lat);
    const dLng = parseFloat(mapDiv.dataset.lng);
    const startLat = isFinite(dLat) ? dLat : -12.0464;  // Lima
    const startLng = isFinite(dLng) ? dLng : -77.0428;
    const startZoom = (isFinite(dLat) && isFinite(dLng)) ? 14 : 12;

    if (typeof L === 'undefined') {
      console.error('Leaflet no está cargado.');
      return;
    }

    const map = L.map(mapDiv);
    map.setView([startLat, startLng], startZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19, attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    function setLatLng(lat, lng, moveMarker = true) {
      latInput.value = Number(lat).toFixed(6);
      lngInput.value = Number(lng).toFixed(6);
      if (moveMarker) marker.setLatLng([lat, lng]);
    }

    // Click y drag
    map.on('click', e => setLatLng(e.latlng.lat, e.latlng.lng));
    marker.on('dragend', () => {
      const p = marker.getLatLng();
      setLatLng(p.lat, p.lng, false);
    });

    // Cambios manuales
    function syncFromInputs() {
      const lat = parseFloat(latInput.value);
      const lng = parseFloat(lngInput.value);
      if (!isFinite(lat) || !isFinite(lng)) return;
      setLatLng(lat, lng);
      map.setView([lat, lng], 16);
      setTimeout(() => map.invalidateSize(true), 100);
    }
    latInput.addEventListener('change', syncFromInputs);
    lngInput.addEventListener('change', syncFromInputs);

    // Geolocalización
    const btnGeo = document.getElementById('btn-geo');
    if (btnGeo) {
      btnGeo.addEventListener('click', () => {
        if (!navigator.geolocation) return alert('Tu navegador no permite geolocalización.');
        navigator.geolocation.getCurrentPosition(
          pos => {
            const { latitude: lat, longitude: lng } = pos.coords;
            setLatLng(lat, lng);
            map.setView([lat, lng], 16);
            setTimeout(() => map.invalidateSize(true), 100);
          },
          () => alert('No se pudo obtener tu ubicación. Activa GPS/ubicación.')
        );
      });
    }

    // Geocoder (lupa)
    if (L.Control && L.Control.geocoder) {
      L.Control.geocoder({ defaultMarkGeocode: false })
        .on('markgeocode', e => {
          const c = e.geocode.center;
          setLatLng(c.lat, c.lng);
          map.setView(c, 16);
          if (addrInput && e.geocode && e.geocode.name) addrInput.value = e.geocode.name;
          setTimeout(() => map.invalidateSize(true), 100);
        })
        .addTo(map);
    }

    // Forzar recálculo (evita “mapa gris”)
    setTimeout(() => map.invalidateSize(true), 150);
  }

  // Ejecuta siempre (DOM cargado o no)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLeafletClientForm, { once: true });
  } else {
    initLeafletClientForm();
  }
  window.addEventListener('load', () => {
    if (!window.__clientLeafletInited) initLeafletClientForm();
  }, { once: true });
})();
</script>

