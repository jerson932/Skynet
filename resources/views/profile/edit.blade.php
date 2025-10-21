<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            @if(optional(auth()->user())->isTecnico())
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-semibold mb-2">Mi supervisor</h3>
                    @php $supervisor = auth()->user()->supervisor; @endphp
                    @if($supervisor)
                        <div class="space-y-1 text-sm text-gray-700">
                            <div><strong>Nombre:</strong> {{ $supervisor->name }}</div>
                            <div><strong>Email:</strong> {{ $supervisor->email }}</div>
                        </div>
                    @else
                        <div class="text-sm text-gray-600">No tienes un supervisor asignado.</div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
