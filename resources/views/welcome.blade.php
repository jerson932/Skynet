<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (app()->environment('production'))
        <!-- Usar Tailwind CSS desde CDN en producción -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            'custom-bg': '#FDFDFC',
                            'custom-dark': '#0a0a0a',
                            'custom-text': '#1b1b18'
                        }
                    }
                }
            }
        </script>
    @else
        <!-- Desarrollo local con Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col"
    style="background: url('{{ asset('images/fondo.jpg') }}') center/cover no-repeat fixed;"
>
    

    <!-- Contenido: SOLO tu panel -->
    <main class="w-full lg:max-w-4xl">
        <div class="relative rounded-lg overflow-hidden bg-white/80 dark:bg-gray-900/70 backdrop-blur p-8 border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <!-- Logo -->
                <div class="flex items-center justify-center w-full lg:w-[45%]">
                    <img
                        src="{{ asset('images/skynet-logo.png') }}"
                        alt="Skynet Systems"
                        class="max-w-[320px] w-full"
                    >
                </div>

                <!-- Texto y botones -->
                <div class="w-full lg:w-[55%]">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Bienvenido a <span class="text-blue-600">Skynet Systems</span>
                    </h1>
                    <p class="text-gray-700 dark:text-gray-300 mb-6">
                        Plataforma de gestión de visitas y supervisión técnica.
                        Por favor, inicia sesión para continuar.
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">
                            Iniciar sesión
                        </a>
                        
                <!-- @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="px-4 py-2 rounded-lg bg-gray-200 text-gray-900 font-semibold hover:bg-gray-300 transition">
                                Registrarse
                            </a>
                        @endif -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    @if (Route::has('login'))
        <div class="h-14.5 hidden lg:block"></div>
    @endif
</body>
</html>
