@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <h1 class="text-3xl font-extrabold mb-6">Crear Usuario</h1>

    @include('partials.flash')

    <form action="{{ route('settings.store') }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Rol</label>
                <select id="role-select" name="role" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}" {{ old('role') == $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                @error('role') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div id="supervisor-wrapper" class="hidden">
                <label class="block text-sm font-medium text-gray-700">Supervisor (opcional)</label>
                <select name="supervisor_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm">
                    <option value="">-- Ninguno --</option>
                    @foreach($supervisors as $s)
                        <option value="{{ $s->id }}" {{ old('supervisor_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('supervisor_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Contraseña (opcional)</label>
            <input type="text" name="password" value="{{ old('password') }}" placeholder="#1company" class="mt-1 block w-full rounded border-gray-300 shadow-sm">
            <p class="text-sm text-gray-500 mt-1">Si no se indica, la contraseña por defecto será <strong>#1company</strong> y se pedirá cambiarla al primer login.</p>
            @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Crear</button>
            <a href="{{ route('settings.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const roleSelect = document.getElementById('role-select');
        const supWrap = document.getElementById('supervisor-wrapper');

        function toggleSupervisor(){
            const val = roleSelect.value;
            // show supervisor select only when role is 'tecnico'
            if(val === 'tecnico') supWrap.classList.remove('hidden'); else supWrap.classList.add('hidden');
        }

        roleSelect.addEventListener('change', toggleSupervisor);
        toggleSupervisor();
    });
</script>
@endpush

@endsection
