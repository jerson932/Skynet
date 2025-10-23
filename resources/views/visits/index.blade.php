<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Visitas
                @if(request('date'))
                    <span class="text-gray-500">({{ request('date') }})</span>
                @else
                    <span class="text-gray-500">(todas)</span>
                @endif
            </h2>

            @can('create', App\Models\Visit::class)
            <a href="{{ route('visits.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva visita
            </a>

         
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">

            @include('partials.flash')

            {{-- Filtro por fecha (opcional) --}}
            <form method="GET" class="flex w-full flex-col items-start gap-3 sm:flex-row sm:items-center">
                <label class="text-sm text-gray-600">Fecha</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="w-full max-w-xs rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <button
                    class="rounded-lg bg-gray-900 px-4 py-2 text-white shadow hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-600">
                    Filtrar
                </button>
                @if(request('date'))
                    <a href="{{ route('visits.index') }}" class="rounded-lg px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Limpiar
                    </a>
                @endif
            </form>

            {{-- Export (XLSX) --}}
            <form id="visits-export-form" method="GET" action="{{ route('visits.export') }}" target="visits-download-iframe" class="flex w-full flex-col items-start gap-2 sm:flex-row sm:items-center">
                <input type="hidden" name="format" value="xlsx">
                <label class="text-sm text-gray-600">Exportar</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full max-w-xs rounded-lg border-gray-300 px-3 py-2 shadow-sm">
                <input type="date" name="to" value="{{ request('to') }}" class="w-full max-w-xs rounded-lg border-gray-300 px-3 py-2 shadow-sm">

                <button id="export-xlsx" type="submit" name="format" value="xlsx" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <svg class="spinner hidden h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="btn-text">Exportar XLSX</span>
                </button>
            </form>
            {{-- Hidden iframe to perform download without leaving the page --}}
            <iframe id="visits-download-iframe" name="visits-download-iframe" style="display:none;width:0;height:0;border:0;"></iframe>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-600">
                                <th class="px-4 py-3 font-medium">#</th>
                                <th class="px-4 py-3 font-medium">Cliente</th>
                                <th class="px-4 py-3 font-medium">Técnico</th>
                                <th class="px-4 py-3 font-medium">Programada</th>
                                <th class="px-4 py-3 font-medium">Check-in</th>
                                <th class="px-4 py-3 font-medium">Check-out</th>
                                <th class="px-4 py-3 font-medium">Estado</th>
                                <th class="px-4 py-3 text-right font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($visits as $v)
                                @php
                                    $status = $v->check_out_at ? 'completada' : ($v->check_in_at ? 'en curso' : 'pendiente');
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500">{{ $v->id }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $v->client->name }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $v->tecnico->name }}</td>
                                    <td class="px-4 py-3 text-gray-800">
                                        {{ optional($v->scheduled_at)->format('Y-m-d H:i') }}
                                    </td>
                                      
                                    
                                    {{-- Check-in --}}
                                    <td class="px-4 py-3">
                                        @if($v->check_in_at)
                                            <div class="font-medium text-gray-900">{{ $v->check_in_at->format('H:i') }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $v->check_in_lat }}, {{ $v->check_in_lng }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    {{-- Check-out --}}
                                    <td class="px-4 py-3">
                                        @if($v->check_out_at)
                                            <div class="font-medium text-gray-900">{{ $v->check_out_at->format('H:i') }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $v->check_out_lat }}, {{ $v->check_out_lng }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    
                                    {{-- Estado --}}
                                    <td class="px-4 py-3">
                                        @if($status === 'completada')
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Finalizado</span>
                                        @elseif($status === 'en curso')
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Iniciado</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">Pendiente</span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2 flex-wrap">

                                        {{-- Ver detalle (mapa + datos del cliente) --}}
                                        <a href="{{ route('visits.show', $v) }}"
                                        class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        Ver
                                        </a>

                                            {{-- Botones del técnico asignado --}}
                                            @can('mark', $v)
                                                @if(!$v->check_in_at)
                                                    <form action="{{ route('visits.checkin', $v) }}" method="POST" class="inline" onsubmit="return fillGeo(this)">
                                                        @csrf
                                                        <input type="hidden" name="lat">
                                                        <input type="hidden" name="lng">
                                                        <button
                                                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                            Check-in
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($v->check_in_at && !$v->check_out_at)
                                                    <form action="{{ route('visits.checkout', $v) }}" method="POST" class="inline" onsubmit="return fillGeo(this)">
                                                        @csrf
                                                        <input type="hidden" name="lat">
                                                        <input type="hidden" name="lng">
                                                        <button
                                                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                            Check-out
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            {{-- Enviar correo (si el cliente tiene email) --}}
                                            @if(!empty($v->client->email))
                                                <form action="{{ route('visits.sendmail', $v) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button
                                                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                        title="Enviar correo de visita">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/>
                                                        </svg>
                                                        Enviar
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Admin / Supervisor (dueño) pueden editar --}}
                                            @can('update', $v)
                                                <a href="{{ route('visits.edit', $v) }}"
                                                   class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-amber-600">
                                                    Editar
                                                </a>
                                            @endcan

                                            {{-- Solo Admin puede eliminar --}}
                                            @can('delete', $v)
                                                <form action="{{ route('visits.destroy', $v) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('¿Eliminar la visita #{{ $v->id }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-red-700">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">Sin visitas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t px-4 py-3">
                    {{ $visits->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Geolocalización: modal con mapa (Leaflet) para elegir ubicación o usar ubicación automática --}}
    {{-- Leaflet CSS/JS (CDN) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        /* simple modal styles */
        .geo-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:60; }
        .geo-modal { background: white; border-radius: .5rem; width: 90%; max-width: 900px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); overflow: hidden; }
    .geo-modal .map { height: 350px; }
    /* ensure leaflet tiles and markers render in modal */
    #geo-map { width: 100%; height: 350px; }
        .geo-modal .modal-body { padding: 1rem; }
        .geo-modal .modal-footer { padding: 0.75rem 1rem; display:flex; justify-content:flex-end; gap:0.5rem; }
    </style>

    <div id="geo-modal" class="hidden" aria-hidden="true">
        <div class="geo-modal-backdrop" role="dialog" aria-modal="true">
            <div class="geo-modal">
                <div class="map" id="geo-map"></div>
                <div class="modal-body">
                    <p class="text-sm text-gray-600">Puedes arrastrar el marcador o hacer clic en el mapa para seleccionar la ubicación. También intenta detectar tu ubicación automáticamente.</p>
                </div>
                <div class="modal-footer">
                    <button id="geo-cancel" class="rounded-lg bg-gray-100 px-3 py-2">Cancelar</button>
                    <button id="geo-detect" class="rounded-lg bg-blue-600 text-white px-3 py-2">Detectar ubicación</button>
                    <button id="geo-confirm" class="rounded-lg bg-green-600 text-white px-3 py-2">Confirmar ubicación</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // map modal state
            let map, marker, currentForm = null;

            function showGeoModal(form) {
                currentForm = form;
                const modal = document.getElementById('geo-modal');
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');

                // init map lazily
                setTimeout(()=>{
                    if (!map) initMap();
                    map.invalidateSize();
                    // try to locate roughly
                    map.setView([0,0], 2);
                    tryAutoLocate();
                }, 50);
            }

            function hideGeoModal() {
                const modal = document.getElementById('geo-modal');
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
            }

            function initMap(){
                map = L.map('geo-map', { center: [0,0], zoom: 2 });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                marker = L.marker([0,0], { draggable: true }).addTo(map);

                map.on('click', function(e){
                    marker.setLatLng(e.latlng);
                });

                marker.on('dragend', function(){ /* nothing else needed */ });
            }

            function tryAutoLocate(){
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(function(pos){
                    const lat = pos.coords.latitude, lng = pos.coords.longitude;
                    map.setView([lat,lng], 15);
                    marker.setLatLng([lat,lng]);
                }, function(err){
                    // ignore silently; user can use Detect button or pick manually
                    console.debug('geolocation failed', err && err.message);
                }, { enableHighAccuracy: true, timeout: 10000 });
            }

            // attach to check-in/check-out forms
            document.querySelectorAll('form[onsubmit^="return fillGeo"]').forEach(f => {
                // replace its onsubmit handler to open the modal instead
                f.removeAttribute('onsubmit');
                f.addEventListener('submit', function(e){
                    e.preventDefault();
                    showGeoModal(f);
                });
            });

            document.getElementById('geo-cancel').addEventListener('click', function(){ hideGeoModal(); });
            document.getElementById('geo-detect').addEventListener('click', function(){ tryAutoLocate(); });
            document.getElementById('geo-confirm').addEventListener('click', function(){
                if (!currentForm || !marker) { hideGeoModal(); return; }
                const pos = marker.getLatLng();
                const latInput = currentForm.querySelector('input[name="lat"]');
                const lngInput = currentForm.querySelector('input[name="lng"]');
                if (!latInput || !lngInput) { hideGeoModal(); return; }
                latInput.value = pos.lat.toFixed(6);
                lngInput.value = pos.lng.toFixed(6);
                hideGeoModal();
                // submit the form after a short delay to allow modal to close
                setTimeout(()=> currentForm.submit(), 150);
            });

            // keep a fallback manual prompt in case Leaflet fails to load
            window.fillGeo = async function(form) {
                // This is kept for backward compatibility but should not be called anymore
                const lat = form.querySelector('input[name="lat"]');
                const lng = form.querySelector('input[name="lng"]');
                const value = window.prompt('Introduce tus coordenadas manualmente en el formato: lat,lng');
                if (!value) return false;
                const parts = value.split(',').map(s => s.trim());
                if (parts.length !== 2) return false;
                lat.value = parseFloat(parts[0]).toFixed(6);
                lng.value = parseFloat(parts[1]).toFixed(6);
                return true;
            };
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('visits-export-form');
            const btn = document.getElementById('export-xlsx');
            const iframe = document.getElementById('visits-download-iframe');
            if (!form || !btn) return;

            let exportTimeout = null;
            const EXPORT_TIMEOUT_MS = 45000; // 45 seconds fallback

            form.addEventListener('submit', function (e) {
                // show spinner and disable button to prevent double-click
                const spinner = btn.querySelector('.spinner');
                const text = btn.querySelector('.btn-text');
                if (spinner) spinner.classList.remove('hidden');
                if (text) text.textContent = 'Generando...';
                btn.disabled = true;

                // set a fallback timeout to restore the button if iframe doesn't fire
                if (exportTimeout) clearTimeout(exportTimeout);
                exportTimeout = setTimeout(() => {
                    if (spinner) spinner.classList.add('hidden');
                    if (text) text.textContent = 'Exportar XLSX';
                    btn.disabled = false;
                    // optional: notify user
                    try { window.alert('La generación tardó demasiado. Intenta nuevamente o revisa la conexión.'); } catch (e) {}
                }, EXPORT_TIMEOUT_MS);
            });

            // when iframe finishes loading (download completed or error page), restore button
            if (iframe) {
                iframe.addEventListener('load', function () {
                    if (exportTimeout) { clearTimeout(exportTimeout); exportTimeout = null; }
                    const spinner = btn.querySelector('.spinner');
                    const text = btn.querySelector('.btn-text');
                    if (spinner) spinner.classList.add('hidden');
                    if (text) text.textContent = 'Exportar XLSX';
                    btn.disabled = false;
                });

                // if user returns focus (maybe blocked permission dialogs), restore state
                window.addEventListener('focus', function () {
                    if (exportTimeout) { clearTimeout(exportTimeout); exportTimeout = null; }
                    const spinner = btn.querySelector('.spinner');
                    const text = btn.querySelector('.btn-text');
                    if (spinner) spinner.classList.add('hidden');
                    if (text) text.textContent = 'Exportar XLSX';
                    btn.disabled = false;
                });
            }
        });
    </script>
</x-app-layout>
