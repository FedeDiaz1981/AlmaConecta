@extends('layouts.app')

@section('title', 'Mi cuenta')

@section('content')
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Título --}}
            <div>
                <h1 class="text-2xl font-semibold text-silver">
                    Mi cuenta
                </h1>
                <p class="mt-1 text-sm text-silver/70">
                    Actualizá tus datos de acceso y tu información básica de usuario.
                </p>
            </div>

            {{-- Datos de perfil (nombre, email, etc.) --}}
            <section class="bg-blueNight/80 border border-blueMid rounded-2xl shadow-soft p-5 sm:p-6">
                <header class="mb-4">
                    <h2 class="text-lg font-semibold text-silver">
                        Información de perfil
                    </h2>
                    <p class="mt-1 text-sm text-silver/70">
                        Estos son tus datos de cuenta (nombre y correo de acceso).
                    </p>
                </header>

                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            {{-- Cambio de contraseña --}}
            <section class="bg-blueNight/80 border border-blueMid rounded-2xl shadow-soft p-5 sm:p-6">
                <header class="mb-4">
                    <h2 class="text-lg font-semibold text-silver">
                        Cambiar contraseña
                    </h2>
                    <p class="mt-1 text-sm text-silver/70">
                        Asegurate de usar una contraseña larga y segura.
                    </p>
                </header>

                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </section>

            {{-- Eliminar cuenta --}}
            <section class="bg-blueNight/80 border border-red-900/60 rounded-2xl shadow-soft p-5 sm:p-6">
                <header class="mb-4">
                    <h2 class="text-lg font-semibold text-red-300">
                        Eliminar cuenta
                    </h2>
                    <p class="mt-1 text-sm text-red-200/80">
                        Esta acción es permanente. Se eliminarán tus datos de acceso y ya no podrás ingresar.
                    </p>
                </header>

                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>

        </div>
    </div>
@endsection
