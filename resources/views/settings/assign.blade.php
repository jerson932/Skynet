@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Asignar técnicos a {{ $user->name }}</h1>
        <a href="{{ route('settings.index') }}" class="px-4 py-2 bg-gray-200 rounded">Volver</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form method="POST" action="{{ route('settings.assign.store', $user) }}">
            @csrf

            <div class="space-y-2">
                @foreach($tecnicos as $t)
                    @php $assignedToOther = $t->supervisor_id && $t->supervisor_id !== $user->id; @endphp
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="tecnicos[]" value="{{ $t->id }}" {{ $t->supervisor_id == $user->id ? 'checked' : '' }} {{ $assignedToOther ? 'disabled' : '' }}>
                        <span class="ml-2">{{ $t->name }} — <small class="text-gray-500">{{ $t->email }}</small>
                            @if($assignedToOther)
                                <span class="ml-2 text-xs text-red-500">(Asignado a {{ optional($t->supervisor)->name }})</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="mt-4 text-right">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar asignaciones</button>
            </div>
        </form>
    </div>
</div>
@endsection
