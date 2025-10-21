<nav x-data="{ open: false }">
    <!-- Thin utility bar (dark) -->
    <div class="bg-gray-900 text-gray-100 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-8">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="hover:text-white">Dashboard</a>
                    <a href="{{ route('visits.index') }}" class="hover:text-white">Visitas</a>
                    @can('create', App\Models\Client::class)
                        <a href="{{ route('clients.web.index') }}" class="hover:text-white">Clientes</a>
                    @endcan
                    @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor())
                        <a href="{{ route('settings.index') }}" class="hover:text-white">Ajustes</a>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-300">
                    <span>{{ config('app.name', 'Skynet') }}</span>
                    <a href="#" class="hover:text-white">Soporte</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main nav -->
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="flex-1 flex justify-center">
                    <div class="relative w-full max-w-2xl">
                        <input id="global-search" type="search" placeholder="Buscar técnico, supervisor o email..." class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div id="search-suggestions" class="absolute z-50 mt-1 left-0 right-0 bg-white border border-gray-200 rounded shadow hidden"></div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button id="advanced-toggle" class="text-sm rounded px-3 py-1 border border-gray-200 bg-white hover:bg-gray-50">Avanzado</button>

                    <!-- Settings / User dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                                @if(Auth::user()->isAdmin() || Auth::user()->isSupervisor())
                                    <x-dropdown-link :href="route('settings.index')">{{ __('Ajustes') }}</x-dropdown-link>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Advanced panel (positioned relative to whole nav) --}}
        <div id="advanced-panel" class="hidden absolute inset-x-0 top-16 flex justify-center pointer-events-none z-50">
            <div class="pointer-events-auto bg-white border border-gray-200 p-4 rounded shadow-md w-full max-w-2xl">
                <form id="advanced-search-form" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-600">Fecha desde</label>
                        <input type="date" name="from" class="w-full mt-1 rounded border-gray-200 px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Fecha hasta</label>
                        <input type="date" name="to" class="w-full mt-1 rounded border-gray-200 px-2 py-1 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Técnico</label>
                        <input type="text" id="adv-tecnico" name="tecnico" class="w-full mt-1 rounded border-gray-200 px-2 py-1 text-sm" placeholder="nombre o email">
                    </div>
                    <div class="sm:col-span-3 flex justify-end gap-2 mt-2">
                        <button type="button" id="adv-apply" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Aplicar</button>
                        <button type="button" id="adv-close" class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    (function(){
        const input = document.getElementById('global-search');
        const sugg = document.getElementById('search-suggestions');
        const advToggle = document.getElementById('advanced-toggle');
        const advPanel = document.getElementById('advanced-panel');
        const advClose = document.getElementById('adv-close');
        const advApply = document.getElementById('adv-apply');

        let debounce = null;
        if (input) {
            input.addEventListener('input', function(){
                const q = this.value.trim();
                if (debounce) clearTimeout(debounce);
                if (!q) { if(sugg) sugg.classList.add('hidden'); return; }
                debounce = setTimeout(()=>{
                    fetch(`/_search/users?q=${encodeURIComponent(q)}`)
                        .then(r=>r.json())
                        .then(data=>{
                            if (!sugg) return;
                            if (!data || !data.length) { sugg.innerHTML = '<div class="p-2 text-sm text-gray-500">Sin resultados</div>'; sugg.classList.remove('hidden'); return; }
                            sugg.innerHTML = data.map(u=>`<a href="/visits?tecnico=${u.id}" class="block px-3 py-2 text-sm hover:bg-gray-50">${u.name} <span class="text-xs text-gray-400">${u.email}</span></a>`).join('');
                            sugg.classList.remove('hidden');
                        }).catch(()=>{ if(sugg) sugg.classList.add('hidden'); });
                }, 300);
            });

            document.addEventListener('click', function(e){
                if (!input.contains(e.target) && !(sugg && sugg.contains(e.target))) {
                    if(sugg) sugg.classList.add('hidden');
                }
            });
        }

        if (advToggle && advPanel) {
            advToggle.addEventListener('click', function(){ advPanel.classList.toggle('hidden'); });
            advClose?.addEventListener('click', function(){ advPanel.classList.add('hidden'); });
            advApply?.addEventListener('click', function(){
                const form = document.getElementById('advanced-search-form');
                const params = new URLSearchParams();
                const from = form.querySelector('input[name=from]').value;
                const to = form.querySelector('input[name=to]').value;
                const tecnico = form.querySelector('input[name=tecnico]').value;
                if (from) params.set('from', from);
                if (to) params.set('to', to);
                if (tecnico) params.set('tecnico', tecnico);
                window.location = '/visits?' + params.toString();
            });
        }
    })();
</script>
