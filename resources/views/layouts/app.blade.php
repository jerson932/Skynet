<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (app()->environment('production'))
            <!-- Usar Tailwind CSS desde CDN en producción -->
            <script src="https://cdn.tailwindcss.com"></script>
            <!-- Alpine.js para interactividad -->
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            fontFamily: {
                                sans: ['Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            }
                        }
                    }
                }
            </script>
            <style>
                /* Estilos adicionales para el sistema */
                .antialiased { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
            </style>
        @else
            <!-- Desarrollo local con Vite -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @include('partials.flash')
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
            <script>
                // Flash animation: fade-in, auto-dismiss with pause-on-hover, manual close
                (function(){
                    const el = document.querySelector('.flash');
                    if (!el) return;

                    // trigger enter animation
                    requestAnimationFrame(()=>{
                        el.classList.remove('opacity-0','translate-y-2');
                        el.classList.add('opacity-100','translate-y-0');
                    });

                    let timeoutMs = 5000;
                    let timer = setTimeout(hideFlash, timeoutMs);

                    function hideFlash(){
                        el.style.transition = 'opacity 0.8s ease, transform 0.6s ease';
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(-8px)';
                        setTimeout(()=> el.remove(), 800);
                    }

                    el.addEventListener('mouseenter', ()=> { clearTimeout(timer); });
                    el.addEventListener('mouseleave', ()=> { timer = setTimeout(hideFlash, 2000); });

                    const btn = el.querySelector('.flash-close');
                    if (btn) btn.addEventListener('click', hideFlash);
                })();
            </script>
        </div>
    </body>
</html>
