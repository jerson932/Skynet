@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">{{ isset($user) ? 'Técnico: '.$user->name : 'Mi supervisor' }}</h2>

        @if($supervisor)
            <div class="space-y-2">
                <div><strong>Nombre:</strong> {{ $supervisor->name }}</div>
                <div><strong>Email:</strong> {{ $supervisor->email }}</div>
            </div>
        @else
            <div class="text-gray-600">No tienes un supervisor asignado.</div>
        @endif
    </div>
</div>
@endsection
