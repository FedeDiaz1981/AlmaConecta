@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    {{-- Overlay tipo modal ocupando la pantalla --}}
    <div class="fixed inset-0 z-40 flex items-stretch sm:items-center justify-center bg-blueDeep/80">

        {{-- Contenedor del modal (full en mobile, tarjeta centrada en desktop) --}}
        <div
            class="relative w-full h-full sm:h-auto sm:max-h-[75vh] sm:w-full max-w-3xl
                   bg-blueNight/95 border border-blueMid rounded-none sm:rounded-2xl shadow-soft
                   overflow-y-auto mx-0 sm:mx-4 p-4 sm:p-6 lg:p-8">

            {{-- Botón cerrar (esquina superior derecha) --}}
            <button
                type="button"
                class="absolute top-3 right-3 inline-flex h-8 w-8 items-center justify-center rounded-full
                       bg-blueDeep/80 text-silver/80 hover:bg-blueDeep hover:text-white
                       border border-blueMid/70 text-sm"
                onclick="if (history.length > 1) { history.back(); } else { window.location.href='{{ route('home') }}'; }"
                aria-label="Cerrar"
            >
                ✕
            </button>

            {{-- Cabecera --}}
            <div class="mb-5 sm:mb-6 pr-8"> {{-- pr-8 para que no choque con el botón cerrar --}}
                <h1 class="text-lg sm:text-xl font-semibold text-silver">
                    Mi perfil
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-silver/70">
                    Estos son tus datos de cuenta (nombre, correo y acceso).
                </p>
            </div>

            {{-- Contenido --}}
            <div class="space-y-4 sm:space-y-6">
                {{-- Datos de la cuenta --}}
                <div class="p-4 sm:p-6 rounded-xl border border-blueMid/60 bg-blueDeep/60">
                    @include('profile.partials.update-profile-information-form')
                </div>

                {{-- Cambio de contraseña --}}
                <div class="p-4 sm:p-6 rounded-xl border border-blueMid/60 bg-blueDeep/60">
                    @include('profile.partials.update-password-form')
                </div>

                {{-- Borrar cuenta --}}
                <div class="p-4 sm:p-6 rounded-xl border border-red-500/40 bg-red-900/20">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
