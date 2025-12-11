@extends('layouts.app')

@section('title', 'Ingresar')

@section('content')
    <div class="py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6">
            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6 sm:p-8">

                <h1 class="text-lg font-semibold text-silver mb-4">
                    Ingresá a tu cuenta
                </h1>

                {{-- Mensaje de estado de sesión --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Correo electrónico" class="text-silver/90" />
                        <x-text-input
                            id="email"
                            class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Password --}}
                    <div>
                        <x-input-label for="password" value="Contraseña" class="text-silver/90" />
                        <x-text-input
                            id="password"
                            class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Recordarme --}}
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input
                                id="remember_me"
                                type="checkbox"
                                class="rounded border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                name="remember"
                            >
                            <span class="ms-2 text-sm text-silver/80">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="text-xs text-silver/70 hover:text-silver underline"
                            >
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end pt-2">
                        <x-primary-button class="bg-gold text-blueDeep hover:bg-goldStrong">
                            Ingresar
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
