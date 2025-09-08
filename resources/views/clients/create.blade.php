<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Nuevo cliente</h2></x-slot>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded shadow">
        <form action="{{ route('clients.web.store') }}" method="POST">
            @include('clients._form')
        </form>
    </div>
</x-app-layout>
