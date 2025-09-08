
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Editar cliente</h2></x-slot>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded shadow">
        <form action="{{ route('clients.web.update', $client) }}" method="POST">
            @method('PUT')
            @include('clients._form', ['client' => $client])
        </form>
    </div>
</x-app-layout>
