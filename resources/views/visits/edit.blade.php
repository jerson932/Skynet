<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar visita #{{ $visit->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('visits.update', $visit) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-medium">Cliente</label>
                        <select name="client_id" class="w-full border rounded px-2 py-1" required>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" @selected(old('client_id',$visit->client_id)==$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium">Técnico</label>
                        <select name="tecnico_id" class="w-full border rounded px-2 py-1" required>
                            @foreach($tecnicos as $t)
                                <option value="{{ $t->id }}" @selected(old('tecnico_id',$visit->tecnico_id)==$t->id)>{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                        @error('tecnico_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium">Fecha y hora programada</label>
                        <input type="datetime-local" name="scheduled_at"
                               value="{{ old('scheduled_at', optional($visit->scheduled_at)->format('Y-m-d\TH:i')) }}"
                               class="w-full border rounded px-2 py-1" required>
                        @error('scheduled_at') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium">Notas</label>
                        <textarea name="notes" class="w-full border rounded px-2 py-1" rows="3">{{ old('notes',$visit->notes) }}</textarea>
                        @error('notes') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('visits.index') }}" class="px-4 py-2 bg-gray-300 rounded">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
