@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('content')
    <div class="pt-4 pb-12 sm:py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6">
            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6 sm:p-8">
                <h1 class="text-lg font-semibold text-silver mb-4">
                    Creá tu cuenta
                </h1>

                @php
                    $accountType = old('account_type', request()->query('account_type', 'provider'));
                    $showClientFields = $accountType === 'client'
                        || $errors->has('document_type')
                        || $errors->has('document_number')
                        || $errors->has('phone');
                @endphp

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-rose-400/40 bg-rose-500/10 px-3 py-2 text-sm text-rose-100">
                        <p class="font-medium">Revisá los campos marcados para continuar:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- Tipo de cuenta -->
                    <div>
                        <x-input-label value="Tipo de cuenta" class="text-silver/90" />

                        <div class="mt-2 flex flex-col gap-2">
                            <label class="flex items-center gap-2 text-sm text-silver/90">
                                <input type="radio"
                                       name="account_type"
                                       value="provider"
                                       class="rounded border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                       required
                                       {{ $accountType === 'provider' ? 'checked' : '' }}>
                                <span>Soy profesional</span>
                            </label>

                            <label class="flex items-center gap-2 text-sm text-silver/90">
                                <input type="radio"
                                       name="account_type"
                                       value="client"
                                       class="rounded border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                       required
                                       {{ $accountType === 'client' ? 'checked' : '' }}>
                                <span>Busco profesional</span>
                            </label>
                        </div>

                        <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
                    </div>

                    <!-- Nombre -->
                    <div>
                        <x-input-label for="name" value="Nombre y apellido" class="text-silver/90" />
                        <x-text-input id="name"
                                      class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                                      type="text"
                                      name="name"
                                      :value="old('name')"
                                      required
                                      autofocus
                                      autocomplete="name"
                                      placeholder="Tu nombre completo" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" value="Correo electrónico" class="text-silver/90" />
                        <x-text-input id="email"
                                      class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                                      type="email"
                                      name="email"
                                      :value="old('email')"
                                      required
                                      autocomplete="username"
                                      placeholder="tunombre@email.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Datos para clientes -->
                    <div id="client-fields"
                         class="space-y-4 {{ $showClientFields ? '' : 'hidden' }}">
                        <div>
                            <x-input-label for="document_type" value="Tipo de documento" class="text-silver/90" />
                            <x-text-input id="document_type"
                                          class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                                          type="text"
                                          name="document_type"
                                          :value="old('document_type')"
                                          data-client-required="1"
                                          placeholder="DNI, pasaporte, etc." />
                            <x-input-error :messages="$errors->get('document_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="document_number" value="Documento" class="text-silver/90" />
                            <x-text-input id="document_number"
                                          class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                                          type="text"
                                          name="document_number"
                                          :value="old('document_number')"
                                          data-client-required="1"
                                          placeholder="Número de documento" />
                            <x-input-error :messages="$errors->get('document_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" value="Teléfono" class="text-silver/90" />
                            <x-text-input id="phone"
                                          class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                                          type="text"
                                          name="phone"
                                          :value="old('phone')"
                                          data-client-required="1"
                                          placeholder="Teléfono de contacto" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <x-input-label for="password" value="Contraseña" class="text-silver/90" />
                        <x-text-input id="password"
                                      class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                                      type="password"
                                      name="password"
                                      required
                                      autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirmar contraseña -->
                    <div>
                        <x-input-label for="password_confirmation" value="Confirmar contraseña" class="text-silver/90" />
                        <x-text-input id="password_confirmation"
                                      class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                                      type="password"
                                      name="password_confirmation"
                                      required
                                      autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a class="text-sm text-silver/70 hover:text-silver underline" href="{{ route('login') }}">
                            ¿Ya tenés cuenta? Ingresá
                        </a>

                        <x-primary-button class="bg-gold text-blueDeep hover:bg-goldStrong">
                            Crear cuenta
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const radios = document.querySelectorAll('input[name="account_type"]');
            const clientFields = document.getElementById('client-fields');
            const clientRequired = document.querySelectorAll('[data-client-required="1"]');

            const toggleClientFields = () => {
                const selected = document.querySelector('input[name="account_type"]:checked')?.value;
                const isClient = selected === 'client';

                if (clientFields) {
                    clientFields.classList.toggle('hidden', !isClient);
                }

                clientRequired.forEach((el) => {
                    if (isClient) {
                        el.setAttribute('required', 'required');
                    } else {
                        el.removeAttribute('required');
                    }
                });
            };

            radios.forEach((r) => r.addEventListener('change', toggleClientFields));
            toggleClientFields();
        });
    </script>
@endsection
