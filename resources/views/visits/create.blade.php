<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear nueva visita
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('visits.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-medium">Cliente</label>
                        <select name="client_id" class="w-full border rounded px-2 py-1" required>
                            <option value="">-- Selecciona un cliente --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium">Técnico</label>
                        <select name="tecnico_id" class="w-full border rounded px-2 py-1" required>
                            <option value="">-- Selecciona un técnico --</option>
                            @foreach($tecnicos as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                        @error('tecnico_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium">Fecha y hora programada</label>
                        {{-- datetime-local necesita formato Y-m-d\TH:i --}}
                        <input type="datetime-local" name="scheduled_at" class="w-full border rounded px-2 py-1" required>
                        @error('scheduled_at') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-medium">Notas</label>
                        <textarea name="notes" class="w-full border rounded px-2 py-1" rows="3"></textarea>
                        @error('notes') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    {{-- 🔵 BOTONES --}}
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('visits.index') }}" class="px-4 py-2 bg-gray-300 rounded">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
