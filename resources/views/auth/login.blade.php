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
                                id="open-forgot-modal"
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

    {{-- Modal: recuperar contraseña --}}
    <div id="forgot-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-md mx-4 bg-blueNight border border-blueMid rounded-2xl shadow-soft overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-blueMid/60">
                <h2 class="text-sm font-semibold text-silver">Recuperar contraseña</h2>
                <button type="button" id="close-forgot-modal" class="text-silver/60 hover:text-silver text-sm">✕</button>
            </div>

            <div class="p-4 sm:p-6 space-y-4">
                @if (session('passwordResetStatus'))
                    <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-200">
                        {{ session('passwordResetStatus') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="forgot_email" value="Correo electrónico" class="text-silver/90" />
                        <x-text-input
                            id="forgot_email"
                            class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autocomplete="email"
                        />
                        <x-input-error :messages="$errors->passwordReset->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" id="cancel-forgot-modal"
                                class="px-3 py-1.5 rounded-md border border-blueMid/60 text-xs text-silver hover:bg-blueMid/20">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-1.5 rounded-md bg-gold text-blueDeep text-xs font-semibold hover:bg-goldStrong">
                            Enviar link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const openBtn = document.getElementById('open-forgot-modal');
            const modal = document.getElementById('forgot-modal');
            const closeBtn = document.getElementById('close-forgot-modal');
            const cancelBtn = document.getElementById('cancel-forgot-modal');
            const emailInput = document.getElementById('forgot_email');

            const openModal = () => {
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                if (emailInput) emailInput.focus();
            };

            const closeModal = () => {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            if (openBtn) {
                openBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal();
                });
            }
            closeBtn && closeBtn.addEventListener('click', closeModal);
            cancelBtn && cancelBtn.addEventListener('click', closeModal);
            modal && modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeModal();
            });

            @if ($errors->passwordReset->any() || session('passwordResetStatus'))
                openModal();
            @endif
        });
    </script>
@endsection
