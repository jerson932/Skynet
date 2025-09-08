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
        <input name="address" value="{{ old('address', $client->address ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <label class="block">Latitud
        <input name="lat" type="number" step="0.000001" value="{{ old('lat', $client->lat ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <label class="block">Longitud
        <input name="lng" type="number" step="0.000001" value="{{ old('lng', $client->lng ?? '') }}" class="mt-1 w-full border rounded px-3 py-2">
    </label>
</div>
<div class="mt-4 flex justify-end gap-2">
    <a href="{{ route('clients.web.index') }}" class="px-4 py-2 bg-gray-300 rounded">Cancelar</a>
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
</div>
