@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Editar usuario</h1>
        <a href="{{ route('settings.index') }}" class="px-4 py-2 bg-gray-200 rounded">Volver</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form method="POST" action="{{ route('settings.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium">Nombre</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded border-gray-300" required />
                @error('name')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Email</label>
                <input name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded border-gray-300" required />
                @error('email')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Rol</label>
                <select name="role" class="mt-1 block w-full rounded border-gray-300" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}" {{ old('role', optional($user->role)->slug) == $r->slug ? 'selected' : '' }}>{{ ucfirst($r->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Supervisor</label>
                <select name="supervisor_id" class="mt-1 block w-full rounded border-gray-300">
                    <option value="">- Ninguno -</option>
                    @foreach($supervisors as $s)
                        <option value="{{ $s->id }}" {{ old('supervisor_id', $user->supervisor_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
