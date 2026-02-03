<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        @php
            $accountType = old('account_type', request()->query('account_type', 'provider'));
        @endphp

        <!-- Account Type -->
        <div>
            <x-input-label :value="__('Tipo de cuenta')" />

            <div class="mt-2 flex flex-col gap-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio"
                           name="account_type"
                           value="provider"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                           {{ $accountType === 'provider' ? 'checked' : '' }}>
                    <span>Soy profesional</span>
                </label>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio"
                           name="account_type"
                           value="client"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                           {{ $accountType === 'client' ? 'checked' : '' }}>
                    <span>Busco profesional</span>
                </label>
            </div>

            <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Client fields -->
        <div id="client-fields"
             class="mt-4 space-y-4 {{ $accountType === 'client' ? '' : 'hidden' }}">
            <div>
                <x-input-label for="document_type" :value="__('Tipo de documento')" />
                <x-text-input id="document_type"
                              class="block mt-1 w-full"
                              type="text"
                              name="document_type"
                              :value="old('document_type')"
                              data-client-required="1"
                              placeholder="DNI, Pasaporte, etc." />
                <x-input-error :messages="$errors->get('document_type')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="document_number" :value="__('Documento')" />
                <x-text-input id="document_number"
                              class="block mt-1 w-full"
                              type="text"
                              name="document_number"
                              :value="old('document_number')"
                              data-client-required="1"
                              placeholder="Número de documento" />
                <x-input-error :messages="$errors->get('document_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Teléfono')" />
                <x-text-input id="phone"
                              class="block mt-1 w-full"
                              type="text"
                              name="phone"
                              :value="old('phone')"
                              data-client-required="1"
                              placeholder="Teléfono de contacto" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

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
</x-guest-layout>
