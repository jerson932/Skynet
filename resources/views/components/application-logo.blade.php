<!-- Usar siempre logo de texto en producción para evitar problemas de archivos -->
@if (app()->environment('production'))
    <div {{ $attributes->merge(['class' => 'h-16 w-auto mx-auto flex items-center justify-center bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-xl rounded-lg px-6 py-2 shadow-lg']) }}>
        SKYNET
    </div>
@else
    <!-- En desarrollo local usar la imagen -->
    <img 
        src="{{ asset('images/skynet-logo.png') }}" 
        alt="Skynet Logo" 
        {{ $attributes->merge(['class' => 'h-16 w-auto mx-auto']) }}
    >
@endif