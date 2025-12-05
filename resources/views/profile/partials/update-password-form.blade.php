<section>
    <header>
        <h2 class="text-lg font-semibold text-silver">
            Actualizar contraseña
        </h2>

        <p class="mt-1 text-sm text-silver/80">
            Usá una contraseña larga y difícil de adivinar para mantener tu cuenta segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label
                for="update_password_current_password"
                value="Contraseña actual"
                class="text-silver/90"
            />
            <x-text-input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label
                for="update_password_password"
                value="Nueva contraseña"
                class="text-silver/90"
            />
            <x-text-input
                id="update_password_password"
                name="password"
                type="password"
                class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label
                for="update_password_password_confirmation"
                value="Confirmar contraseña"
                class="text-silver/90"
            />
            <x-text-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="bg-gold text-blueDeep hover:bg-goldStrong">
                Guardar
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-300"
                >
                    Contraseña actualizada.
                </p>
            @endif
        </div>
    </form>
</section>
