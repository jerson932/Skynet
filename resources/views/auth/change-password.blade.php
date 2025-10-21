@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Cambiar contraseña</h1>

    <div class="bg-white shadow rounded p-6">
        @include('partials.flash')

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nueva contraseña</label>
                <input type="password" name="password" required class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
                @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" required class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-600">Volver</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Actualizar contraseña</button>
            </div>
        </form>
    </div>
</div>
@endsection
