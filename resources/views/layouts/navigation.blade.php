{{-- resources/views/layouts/navigation.blade.php --}}
@php
    $isAdmin = auth()->check() && Gate::allows('admin');
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <!-- Primary Nav -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left: Logo + Main Links -->
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto text-gray-800" />
                        <span class="sr-only">Inicio</span>
                    </a>
                </div>

                <!-- Desktop: Nav Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <!-- Home: visible siempre -->
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        Inicio
                    </x-nav-link>

                    <!-- Dashboard: solo si está autenticado -->
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Dashboard
                        </x-nav-link>
                    @endauth

                    <!-- Enlaces de administración: solo admin -->
                    @if($isAdmin)
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">
                            Cuentas
                        </x-nav-link>

                        <x-nav-link :href="route('admin.edits.index')" :active="request()->routeIs('admin.edits.index')">
                            Cambios
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Right: Auth / User Menu -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                    <!-- User Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4
                                       font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none
                                       transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Perfil -->
                            <x-dropdown-link :href="route('profile.edit')">
                                Mi cuenta
                            </x-dropdown-link>

                            <!-- Acceso rápido admin -->
                            @if($isAdmin)
                                <x-dropdown-link :href="route('admin.dashboard')">
                                    Panel admin
                                </x-dropdown-link>
                            @endif

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault(); this.closest('form').submit();">
                                    Cerrar sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}"
                           class="text-sm text-gray-700 hover:text-gray-900">Ingresar</a>
                        <a href="{{ route('register') }}"
                           class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Registrarse</a>
                    </div>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700
                               hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }"
                              class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }"
                              class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile: Responsive Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <!-- Home -->
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                Inicio
            </x-responsive-nav-link>

            <!-- Dashboard solo autenticado -->
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-responsive-nav-link>
            @endauth

            <!-- Admin solo si Gate::allows('admin') -->
            @if($isAdmin)
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">
                    Cuentas
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.edits.index')" :active="request()->routeIs('admin.edits.index')">
                    Cambios
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Mobile: Auth -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-600">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        Mi cuenta
                    </x-responsive-nav-link>

                    @if($isAdmin)
                        <x-responsive-nav-link :href="route('admin.dashboard')">
                            Panel admin
                        </x-responsive-nav-link>
                    @endif

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                               onclick="event.preventDefault(); this.closest('form').submit();">
                            Cerrar sesión
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-gray-200">
                <div class="px-4 space-y-2">
                    <a href="{{ route('login') }}" class="block text-gray-700">Ingresar</a>
                    <a href="{{ route('register') }}" class="block text-indigo-600 font-medium">Registrarse</a>
                </div>
            </div>
        @endauth
    </div>
</nav>
