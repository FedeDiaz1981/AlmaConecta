<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-red-300">
            Eliminar cuenta
        </h2>

        <p class="mt-1 text-sm text-silver/80">
            Una vez que elimines tu cuenta, todos tus datos se borrarán de forma permanente.
            Si necesitás conservar algo, descargalo o guardalo antes de continuar.
        </p>
    </header>

    {{-- Botón que abre el modal de confirmación --}}
    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 hover:bg-red-500 focus:ring-red-500"
    >
        Eliminar cuenta
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-blueNight text-silver">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-red-200">
                ¿Seguro que querés eliminar tu cuenta?
            </h2>

            <p class="mt-1 text-sm text-silver/80">
                Esta acción no se puede deshacer. Para confirmar, ingresá tu contraseña.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Contraseña" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4 bg-blueDeep border-blueMid text-silver"
                    placeholder="Contraseña"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button
                    x-on:click="$dispatch('close')"
                    class="border-blueMid text-silver hover:bg-blueDeep/60"
                >
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="bg-red-600 hover:bg-red-500 focus:ring-red-500">
                    Eliminar definitivamente
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
