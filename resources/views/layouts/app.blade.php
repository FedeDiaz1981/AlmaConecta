<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Alma Conecta')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blueDeep text-silver min-h-screen flex flex-col">

    {{-- ====================== --}}
    {{-- HEADER TIPO WONOMA     --}}
    {{-- ====================== --}}
    <header class="border-b border-blueNight/30 bg-white text-carbon">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-2 flex items-center justify-between gap-4">

            {{-- IZQUIERDA: LOGO + BUSCADOR --}}
            <div class="flex items-center gap-4 flex-1 min-w-0">

                {{-- Logo texto --}}
                <a href="{{ route('home') }}" class="flex items-baseline gap-1 whitespace-nowrap">
                    <span class="text-lg font-semibold tracking-[0.18em] uppercase text-gold">
                        ALMA
                    </span>
                    <span class="text-lg font-semibold tracking-[0.18em] uppercase text-blueDeep">
                        CONECTA
                    </span>
                </a>

                {{-- Buscador en header --}}
                <form method="GET"
                      action="{{ route('search') }}"
                      class="hidden md:flex items-stretch flex-1 max-w-md border border-gray-300 rounded-lg overflow-hidden bg-white">
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

            {{-- DERECHA: PAÍS + LINKS + BOTÓN --}}
            <div class="hidden md:flex items-center gap-4 text-sm">

                {{-- Selector de país (simple por ahora) --}}
                <button type="button"
                        class="flex items-center gap-1 border border-gray-300 rounded-md px-2 py-1 text-xs hover:bg-gray-50">
                    <span class="w-4 h-3 bg-gray-300 rounded-sm"></span>
                    <span>Argentina</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <a href="{{ route('dashboard.profile.edit') }}" class="hover:text-gold whitespace-nowrap">
                    Publicá tu espacio
                </a>

                <a href="{{ route('register') }}" class="hover:text-gold whitespace-nowrap">
                    Registrarse
                </a>

                @guest
                    <a href="{{ route('login') }}"
                       class="px-4 py-1.5 rounded-md bg-gold text-white text-sm font-semibold hover:bg-goldStrong whitespace-nowrap">
                        Ingresar
                    </a>
                @else
                    <a href="{{ route('dashboard.profile.edit') }}"
                       class="px-4 py-1.5 rounded-md bg-gold text-white text-sm font-semibold hover:bg-goldStrong whitespace-nowrap">
                        Mi cuenta
                    </a>
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

        {{-- MENÚ MOBILE (incluye buscador + links) --}}
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

                    <a href="{{ route('dashboard.profile.edit') }}" class="hover:text-gold">
                        Publicá tu espacio
                    </a>

                    <a href="{{ route('register') }}" class="hover:text-gold">
                        Registrarse
                    </a>

                    @guest
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center px-4 py-1.5 rounded-md bg-gold text-white text-sm font-semibold hover:bg-goldStrong mt-1">
                            Ingresar
                        </a>
                    @else
                        <a href="{{ route('dashboard.profile.edit') }}"
                           class="inline-flex items-center justify-center px-4 py-1.5 rounded-md bg-gold text-white text-sm font-semibold hover:bg-goldStrong mt-1">
                            Mi cuenta
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    {{-- CONTENIDO --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER (lo dejamos como estaba o lo ajustamos después) --}}
    <footer class="border-t border-blueNight/60 bg-blueDeep pt-10 pb-6 mt-10 text-xs text-silver/70">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col md:flex-row justify-between gap-3">
            <span>© {{ date('Y') }} Alma Conecta. Todos los derechos reservados.</span>
            <span class="md:text-right">Punto de encuentro holístico.</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('menuToggle');
            const nav = document.getElementById('mobileNav');
            if (!btn || !nav) return;
            btn.addEventListener('click', () => nav.classList.toggle('hidden'));
        });
    </script>
</body>
</html>
