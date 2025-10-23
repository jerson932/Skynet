@if (file_exists(public_path('images/skynet-logo.png')))
    <img 
        src="{{ asset('images/skynet-logo.png') }}" 
        alt="Skynet Logo" 
        {{ $attributes->merge(['class' => 'h-16 w-auto mx-auto']) }}
    >
@else
    <!-- Fallback: Logo de texto si la imagen no está disponible -->
    <div {{ $attributes->merge(['class' => 'h-16 w-auto mx-auto flex items-center justify-center bg-blue-600 text-white font-bold text-2xl rounded-lg px-4']) }}>
        SKYNET
    </div>
@endif