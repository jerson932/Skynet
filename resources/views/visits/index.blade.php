<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Visitas <span class="text-gray-500">({{ $today }})</span>
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

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Filtro por fecha --}}
            <form method="GET" class="flex w-full flex-col items-start gap-3 sm:flex-row sm:items-center">
                <label class="text-sm text-gray-600">Fecha</label>
                <input type="date" name="date" value="{{ $today }}"
                       class="w-full max-w-xs rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <button
                    class="rounded-lg bg-gray-900 px-4 py-2 text-white shadow hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-600">
                    Filtrar
                </button>
            </form>

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
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                                Completada
                                            </span>
                                        @elseif($status === 'en curso')
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                                En curso
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            @can('mark', $v)
                                                @if(!$v->check_in_at)
                                                    <form action="{{ route('visits.checkin', $v) }}" method="POST" class="inline" onsubmit="return fillGeo(this)">
                                                        @csrf
                                                        <input type="hidden" name="lat">
                                                        <input type="hidden" name="lng">
                                                        <button
                                                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3Zm0 0v8"/>
                                                            </svg>
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
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            Check-out
                                                        </button>
                                                    </form>
                                                @endif
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

    {{-- Geolocalización: rellena lat/lng al enviar Check-in / Check-out --}}
    <script>
        async function fillGeo(form) {
            const lat = form.querySelector('input[name="lat"]');
            const lng = form.querySelector('input[name="lng"]');

            if (!navigator.geolocation) {
                alert('Tu navegador no permite geolocalización.');
                return false;
            }

            const getPosition = () => new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true, timeout: 8000, maximumAge: 0
                });
            });

            try {
                const pos = await getPosition();
                lat.value = pos.coords.latitude.toFixed(6);
                lng.value = pos.coords.longitude.toFixed(6);
                return true; // envía el form
            } catch (e) {
                alert('No se pudo obtener tu ubicación. Activa GPS/ubicación e inténtalo otra vez.');
                return false;
            }
        }
    </script>
</x-app-layout>
