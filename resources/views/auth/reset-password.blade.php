@extends('layouts.app')

@section('title', 'Restablecer contraseña - Alma Conecta')

@section('content')
    <div class="py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6">
            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6 sm:p-8">
                <h1 class="text-lg font-semibold text-silver mb-4">
                    Restablecer contraseña
                </h1>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                    @csrf

                    {{-- Token --}}
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Correo electrónico" class="text-silver/90" />
                        <x-text-input
                            id="email"
                            class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                            type="email"
                            name="email"
                            :value="old('email', $request->email)"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Nueva contraseña --}}
                    <div>
                        <x-input-label for="password" value="Nueva contraseña" class="text-silver/90" />
                        <x-text-input
                            id="password"
                            class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Confirmar contraseña --}}
                    <div>
                        <x-input-label for="password_confirmation" value="Confirmar contraseña" class="text-silver/90" />
                        <x-text-input
                            id="password_confirmation"
                            class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button class="bg-gold text-blueDeep hover:bg-goldStrong">
                            Restablecer
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
