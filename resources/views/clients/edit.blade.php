{{-- resources/views/clients/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar cliente
        </h2>
    </x-slot>

    <div class="p-6 max-w-3xl mx-auto bg-white rounded shadow">
        <form action="{{ route('clients.web.update', $client) }}" method="POST">
            @csrf
            @method('PUT')
            @include('clients._form', ['client' => $client])
        </form>
    </div>
</x-app-layout>
