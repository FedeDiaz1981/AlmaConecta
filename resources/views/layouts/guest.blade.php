<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $routeName = request()->route()?->getName();
            $defaultTitle = match ($routeName) {
                'login' => 'Ingresar - Alma Conecta',
                'register' => 'Crear cuenta - Alma Conecta',
                'password.request' => 'Recuperar contraseña - Alma Conecta',
                'password.confirm' => 'Confirmar contraseña - Alma Conecta',
                'password.reset' => 'Restablecer contraseña - Alma Conecta',
                'verification.notice' => 'Verificar correo - Alma Conecta',
                default => config('app.name', 'Alma Conecta'),
            };
        @endphp
        <title>@yield('title', $defaultTitle)</title>
        <meta name="description" content="@yield('meta_description', 'Alma Conecta conecta personas con profesionales y espacios de bienestar holístico.')">
        <link rel="canonical" href="@yield('canonical', url()->current())">

        @include('partials.tracking-head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @include('partials.tracking-body')

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="{{ route('landing') }}">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" aria-label="Alma Conecta" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
