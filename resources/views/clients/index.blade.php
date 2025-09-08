<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Clientes</h2>
            @can('create', App\Models\Client::class)
            <a href="{{ route('clients.web.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Nuevo</a>
            @endcan
        </div>
    </x-slot>

    <div class="p-6 max-w-5xl mx-auto space-y-4">
        @if(session('status'))
            <div class="bg-green-50 text-green-800 px-4 py-2 rounded">{{ session('status') }}</div>
        @endif

        <form class="flex gap-2">
            <input name="q" value="{{ $q }}" placeholder="Buscar nombre o email" class="border rounded px-3 py-2 w-full max-w-md">
            <button class="px-4 py-2 bg-gray-900 text-white rounded">Buscar</button>
        </form>

        <div class="bg-white rounded border overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">#</th>
                        <th class="px-3 py-2 text-left">Nombre</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">Teléfono</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($clients as $c)
                    <tr>
                        <td class="px-3 py-2">{{ $c->id }}</td>
                        <td class="px-3 py-2">{{ $c->name }}</td>
                        <td class="px-3 py-2">{{ $c->email ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $c->phone ?? '—' }}</td>
                        <td class="px-3 py-2 text-right flex gap-2 justify-end">
                            @can('update', $c)
                            <a href="{{ route('clients.web.edit', $c) }}" class="px-3 py-1 bg-amber-500 text-white rounded">Editar</a>
                            @endcan
                            @can('delete', $c)
                            <form action="{{ route('clients.web.destroy', $c) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1 bg-red-600 text-white rounded">Eliminar</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">Sin clientes</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $clients->withQueryString()->links() }}
    </div>
</x-app-layout>
