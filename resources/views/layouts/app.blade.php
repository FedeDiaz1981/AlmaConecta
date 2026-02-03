<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Alma Conecta')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
     {{-- Favicon de la pestaña --}}
    <link rel="icon" type="image/png" href="{{ asset('logo_sin_fondo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blueDeep text-silver min-h-screen flex flex-col">

    {{-- HEADER --}}
    <header class="border-b border-blueNight/30 bg-white text-carbon">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-2 flex items-center justify-between gap-4">

            {{-- IZQUIERDA: LOGO + BUSCADOR --}}
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2 whitespace-nowrap">
                    <img
                        src="{{ asset('logo_sin_fondo.png') }}"
                        alt="Alma Conecta"
                        class="h-8 w-8 sm:h-9 sm:w-9 rounded-full"
                    >
                    <span class="text-lg font-semibold tracking-[0.18em] uppercase text-gold">
                        ALMA
                    </span>
                    <span class="text-lg font-semibold tracking-[0.18em] uppercase text-blueDeep">
                        CONECTA
                    </span>
                </a>

                {{-- Buscador header (oculto por ahora) --}}
                <form method="GET"
                      action="{{ route('search') }}"
                      class="hidden items-stretch flex-1 max-w-md border border-gray-300 rounded-lg overflow-hidden bg-white">
                    <input
                        type="text"
                        name="q"
                        placeholder="Buscar espacio, práctica, facilitador..."
                        class="flex-1 px-3 py-1.5 text-sm focus:outline-none"
                    >
                    <button type="submit"
                            class="px-3 flex items-center justify-center border-l border-gray-200 hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                    </button>
                </form>
            </div>

            {{-- DERECHA: LINKS / BOTONES (DESKTOP) --}}
            <div class="hidden md:flex items-center gap-3 text-sm">
                @guest
                    {{-- Invitado: un solo link → registro --}}
                    <a href="{{ route('register') }}" class="hover:text-gold whitespace-nowrap">
                        Publicá tu espacio
                    </a>
                    <a href="{{ route('register', ['account_type' => 'client']) }}"
                       class="hover:text-gold whitespace-nowrap">
                        Busco un profesional
                    </a>

                    <a href="{{ route('login') }}"
                       class="px-4 py-1.5 rounded-md bg-gold text-white text-sm font-semibold hover:bg-goldStrong whitespace-nowrap">
                        Ingresar
                    </a>
                @else
                    @php $user = auth()->user(); @endphp

                    {{-- Si es provider, link a su perfil profesional (texto opcional) --}}
                    @if($user->role === 'provider')
                        <a href="{{ route('dashboard.profile.edit') }}"
                           class="hidden lg:inline hover:text-gold whitespace-nowrap">
                            Publicá tu espacio
                        </a>
                    @endif

                    {{-- Si es admin, botón para volver al dashboard --}}
                    @if($user->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-3 py-1.5 rounded-md border border-gold/60 bg-gold/10 text-xs font-semibold text-blueDeep hover:bg-gold/30 whitespace-nowrap">
                            Panel admin
                        </a>
                        <a href="{{ route('admin.reports.index') }}"
                           class="px-3 py-1.5 rounded-md border border-red-400/60 bg-red-500/10 text-xs font-semibold text-red-200 hover:bg-red-500/20 whitespace-nowrap">
                            Cuentas reportadas
                        </a>
                    @endif

                    {{-- Mi cuenta --}}
                    @if($user->role === 'client')
                        <a href="{{ route('profile.edit') }}"
                           class="px-4 py-1.5 rounded-md bg-gold text-blueDeep text-sm font-semibold hover:bg-goldStrong whitespace-nowrap">
                            Mi cuenta
                        </a>
                    @else
                        <button type="button"
                                id="openProfileModal"
                                class="px-4 py-1.5 rounded-md bg-gold text-blueDeep text-sm font-semibold hover:bg-goldStrong whitespace-nowrap">
                            Mi cuenta
                        </button>
                    @endif

                    {{-- Cerrar sesión --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 rounded-md border border-blueNight/30 text-xs text-blueNight hover:bg-blueNight/5 whitespace-nowrap">
                            Cerrar sesión
                        </button>
                    </form>
                @endguest
            </div>

            {{-- BOTÓN MOBILE --}}
            <button id="menuToggle"
                    class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-full border border-gray-300 text-gray-700">
                <span class="sr-only">Abrir menú</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- MENÚ MOBILE --}}
        <div id="mobileNav" class="md:hidden hidden border-t border-gray-200 bg-white text-carbon">
            <div class="max-w-6xl mx-auto px-4 py-3 flex flex-col gap-3 text-sm">

                {{-- Buscador mobile --}}
                <form method="GET"
                      action="{{ route('search') }}"
                      class="flex items-stretch border border-gray-300 rounded-lg overflow-hidden bg-white">
                    <input
                        type="text"
                        name="q"
                        placeholder="Buscar espacio, práctica, facilitador..."
                        class="flex-1 px-3 py-1.5 text-sm focus:outline-none"
                    >
                    <button type="submit"
                            class="px-3 flex items-center justify-center border-l border-gray-200 hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                    </button>
                </form>

                <div class="flex flex-col gap-2 pt-2 border-t border-gray-200 mt-1">
                    <button type="button"
                            class="flex items-center gap-1 border border-gray-300 rounded-md px-2 py-1 text-xs w-max hover:bg-gray-50">
                        <span class="w-4 h-3 bg-gray-300 rounded-sm"></span>
                        <span>Argentina</span>
                    </button>

                    @guest
                        {{-- Invitado: un solo link → registro --}}
                        <a href="{{ route('register') }}" class="hover:text-gold">
                            Publicá tu espacio
                        </a>
                        <a href="{{ route('register', ['account_type' => 'client']) }}" class="hover:text-gold">
                            Busco un profesional
                        </a>

                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center px-4 py-1.5 rounded-md bg-gold text-white text-sm font-semibold hover:bg-goldStrong mt-1">
                            Ingresar
                        </a>
                    @else
                        @php $user = auth()->user(); @endphp

                        @if($user->role === 'provider')
                            <a href="{{ route('dashboard.profile.edit') }}" class="hover:text-gold">
                                Publicá tu espacio
                            </a>
                        @endif

                        {{-- Botón Panel admin en mobile --}}
                        @if($user->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}
                               " class="inline-flex items-center justify-center px-4 py-1.5 rounded-md border border-gold/60 bg-gold/10 text-xs font-semibold text-blueDeep hover:bg-gold/30 mt-1">
                                Panel admin
                            </a>
                            <a href="{{ route('admin.reports.index') }}"
                               class="inline-flex items-center justify-center px-4 py-1.5 rounded-md border border-red-400/60 bg-red-500/10 text-xs font-semibold text-red-200 hover:bg-red-500/20 mt-1">
                                Cuentas reportadas
                            </a>
                        @endif

                        {{-- Mi cuenta --}}
                        @if($user->role === 'client')
                            <a href="{{ route('profile.edit') }}"
                               class="inline-flex items-center justify-center px-4 py-1.5 rounded-md bg-gold text-blueDeep text-sm font-semibold hover:bg-goldStrong mt-1">
                                Mi cuenta
                            </a>
                        @else
                            <button type="button"
                                    id="openProfileModalMobile"
                                    class="inline-flex items-center justify-center px-4 py-1.5 rounded-md bg-gold text-blueDeep text-sm font-semibold hover:bg-goldStrong mt-1">
                                Mi cuenta
                            </button>
                        @endif

                        {{-- Cerrar sesión --}}
                        <form method="POST" action="{{ route('logout') }}" class="mt-1">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-4 py-1.5 rounded-md border border-blueNight/30 text-xs text-blueNight hover:bg-blueNight/5">
                                Cerrar sesión
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    {{-- CONTENIDO --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-blueNight/60 bg-blueDeep pt-10 pb-6 mt-10 text-xs text-silver/70">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col md:flex-row justify-between gap-3">
            <span>© {{ date('Y') }} Alma Conecta. Todos los derechos reservados.</span>
            <span class="md:text-right">Punto de encuentro holístico.</span>
        </div>
    </footer>

    {{-- MODAL PERFIL (reutiliza las vistas de Breeze) --}}
    @auth
        <div id="profileModal"
             class="fixed inset-0 z-40 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="w-full max-w-lg mx-4 bg-blueNight border border-blueMid rounded-2xl shadow-soft overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-blueMid/60">
                    <h2 class="text-sm font-semibold text-silver">
                        Información de perfil
                    </h2>
                    <button type="button" id="closeProfileModal" class="text-silver/60 hover:text-silver text-sm">
                        ✕
                    </button>
                </div>

                <div class="max-h-[80vh] overflow-y-auto p-4 sm:p-6">
                    {{-- Reutilizamos la vista de edición de perfil estándar --}}
                    @include('profile.partials.update-profile-information-form', [
                        'user' => auth()->user(),
                        'profile' => $profile ?? null,
                        'specialties' => auth()->user()->role === 'admin' ? collect() : ($specialties ?? collect()),
                    ])

                    <div class="mt-8 border-t border-blueMid/60 pt-4">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('menuToggle');
            const nav = document.getElementById('mobileNav');
            if (btn && nav) {
                btn.addEventListener('click', () => nav.classList.toggle('hidden'));
            }

            const modal    = document.getElementById('profileModal');
            const openDesk = document.getElementById('openProfileModal');
            const openMob  = document.getElementById('openProfileModalMobile');
            const closeBtn = document.getElementById('closeProfileModal');

            function openModal() {
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
            function closeModal() {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            openDesk && openDesk.addEventListener('click', openModal);
            openMob  && openMob.addEventListener('click', openModal);
            closeBtn && closeBtn.addEventListener('click', closeModal);

            modal && modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeModal();
            });
        });
    </script>
</body>
</html>
