<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Visita #{{ $visit->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">

            {{-- Datos del cliente / visita --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-gray-600">Cliente</h3>
                        <div class="text-lg font-medium text-gray-900">
                            {{ $visit->client->name }}
                        </div>
                        @if($visit->client->contact_name)
                            <div class="text-sm text-gray-600">Contacto: {{ $visit->client->contact_name }}</div>
                        @endif
                        @if($visit->client->phone)
                            <div class="text-sm text-gray-600">Teléfono: {{ $visit->client->phone }}</div>
                        @endif
                        @if($visit->client->email)
                            <div class="text-sm text-gray-600">Email: {{ $visit->client->email }}</div>
                        @endif
                        @if($visit->client->address)
                            <div class="text-sm text-gray-600">Dirección: {{ $visit->client->address }}</div>
                        @endif
                    </div>

                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-gray-600">Asignación</h3>
                        <div class="text-sm text-gray-700">Técnico: {{ $visit->tecnico->name }}</div>
                        <div class="text-sm text-gray-700">Supervisor: {{ $visit->supervisor->name }}</div>
                        <div class="text-sm text-gray-700">Programada:
                            {{ optional($visit->scheduled_at)->format('Y-m-d H:i') }}
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @php
                                $lat = $visit->client->lat;
                                $lng = $visit->client->lng;
                            @endphp
                            @if(!is_null($lat) && !is_null($lng))
                                <a target="_blank"
                                   href="https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}"
                                   class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                   Ver en Google Maps
                                </a>
                                <a target="_blank"
                                   href="https://www.google.com/maps/dir/?api=1&destination={{ $lat }},{{ $lng }}"
                                   class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                   Navegar (Google)
                                </a>
                                <a target="_blank"
                                   href="https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=%3B{{ $lat }},{{ $lng }}"
                                   class="rounded-lg bg-gray-800 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-900">
                                   Navegar (OSM)
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mapa --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-gray-600">Ubicación del cliente</h3>

                @if(!is_null($visit->client->lat) && !is_null($visit->client->lng))
                    <div id="map"
                         data-lat="{{ $visit->client->lat }}"
                         data-lng="{{ $visit->client->lng }}"
                         style="height: 360px; border-radius:.75rem; overflow:hidden; background:#f3f4f6;">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Puedes acercar/alejar el mapa y tocar el marcador para ver coordenadas.
                    </p>
                @else
                    <div class="rounded-lg bg-amber-50 p-4 text-amber-800">
                        Este cliente aún no tiene coordenadas registradas (lat/lng).
                    </div>
                @endif
            </div>

            {{-- Acciones rápidas (técnico) --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap gap-2">
                    @can('mark', $visit)
                        @if(!$visit->check_in_at)
                            <form action="{{ route('visits.checkin', $visit) }}" method="POST" onsubmit="return fillGeo(this)">
                                @csrf
                                <input type="hidden" name="lat">
                                <input type="hidden" name="lng">
                                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                                    Check-in
                                </button>
                            </form>
                        @endif

                        @if($visit->check_in_at && !$visit->check_out_at)
                            <form action="{{ route('visits.checkout', $visit) }}" method="POST" onsubmit="return fillGeo(this)">
                                @csrf
                                <input type="hidden" name="lat">
                                <input type="hidden" name="lng">
                                <button class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                                    Check-out
                                </button>
                            </form>
                        @endif
                    @endcan

                    @if(!empty($visit->client->email))
                        <form action="{{ route('visits.sendmail', $visit) }}" method="POST">
                            @csrf
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                Enviar correo
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Geolocalización (para check-in/out) --}}
    <script>
        async function fillGeo(form) {
            const lat = form.querySelector('input[name="lat"]');
            const lng = form.querySelector('input[name="lng"]');
            if (!navigator.geolocation) { alert('Tu navegador no permite geolocalización.'); return false; }
            const getPosition = () => new Promise((res, rej) => {
                navigator.geolocation.getCurrentPosition(res, rej, { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 });
            });
            try {
                const pos = await getPosition();
                lat.value = pos.coords.latitude.toFixed(6);
                lng.value = pos.coords.longitude.toFixed(6);
                return true;
            } catch(e) {
                alert('No se pudo obtener tu ubicación. Activa GPS/ubicación e inténtalo otra vez.');
                return false;
            }
        }
    </script>

    {{-- Leaflet (solo si hay lat/lng) --}}
    @if(!is_null($visit->client->lat) && !is_null($visit->client->lng))
        @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous"/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
        @endonce

        <script>
        (function(){
            function init() {
                const div = document.getElementById('map');
                if (!div || typeof L === 'undefined') return;

                const lat = parseFloat(div.dataset.lat);
                const lng = parseFloat(div.dataset.lng);
                const map = L.map(div).setView([lat, lng], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19, attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const marker = L.marker([lat, lng]).addTo(map).bindPopup('Cliente').openPopup();
                setTimeout(() => map.invalidateSize(true), 150); // evita “mapa gris”
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init, { once:true });
            } else {
                init();
            }
        })();
        </script>
    @endif
</x-app-layout>
