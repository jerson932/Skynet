@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Mis técnicos</h1>
    </div>

    <div class="bg-white shadow rounded overflow-hidden">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">#</th>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">Nombre</th>
                    <th class="px-4 py-2 text-left text-sm text-gray-600">Email</th>
                    <th class="px-4 py-2 text-right text-sm text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y">
                @foreach($tecnicos as $t)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $t->id }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ $t->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $t->email }}</td>
                    <td class="px-4 py-3 text-sm text-right">
                        <a href="{{ route('settings.tecnico', $t) }}" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tecnicos->withQueryString()->links() }}</div>
</div>
@endsection
