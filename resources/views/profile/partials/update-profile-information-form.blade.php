<section>
    <header>
        <h2 class="text-lg font-semibold text-silver">
            Información de la cuenta
        </h2>

        @php
            $isProvider = $user->role === 'provider';
            $isClient = $user->role === 'client';
        @endphp

        <p class="mt-1 text-sm text-silver/80">
            @if($isProvider)
                Actualizá tu nombre, correo electrónico y las especialidades con las que querés figurar.
            @elseif($isClient)
                Actualizá tu nombre, correo electrónico y tus datos de contacto.
            @else
                Actualizá tu nombre y correo electrónico de acceso.
            @endif
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Nombre --}}
        <div>
            <x-input-label
                for="name"
                value="Nombre"
                class="text-silver/90"
            />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label
                for="email"
                value="Correo electrónico"
                class="text-silver/90"
            />
            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-amber-200">
                        Tu correo todavía no fue verificado.

                        <button
                            form="send-verification"
                            class="underline text-sm text-silver/80 hover:text-silver rounded-md
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold"
                        >
                            Hacé clic acá para reenviar el mail de verificación.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-300">
                            Te enviamos un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Datos de contacto (solo usuarios buscadores) --}}
        @if($isClient)
            <div>
                <x-input-label
                    for="document_type"
                    value="Tipo de documento"
                    class="text-silver/90"
                />
                <x-text-input
                    id="document_type"
                    name="document_type"
                    type="text"
                    class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                    :value="old('document_type', $user->document_type)"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('document_type')" />
            </div>

            <div>
                <x-input-label
                    for="document_number"
                    value="Documento"
                    class="text-silver/90"
                />
                <x-text-input
                    id="document_number"
                    name="document_number"
                    type="text"
                    class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                    :value="old('document_number', $user->document_number)"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('document_number')" />
            </div>

            <div>
                <x-input-label
                    for="phone"
                    value="Teléfono"
                    class="text-silver/90"
                />
                <x-text-input
                    id="phone"
                    name="phone"
                    type="text"
                    class="mt-1 block w-full bg-blueDeep border-blueMid text-silver"
                    :value="old('phone', $user->phone)"
                    required
                />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        @endif

        {{-- Especialidades múltiples: SOLO providers --}}
        @if($isProvider)
            @php
                $selectedSpecialties = old('specialties', isset($profile)
                    ? $profile->specialties->pluck('id')->toArray()
                    : []);
            @endphp

            <div>
                <x-input-label
                    value="Especialidades"
                    class="text-silver/90"
                />

                <p class="mt-1 text-xs text-silver/60">
                    Podés marcar una o varias especialidades con las que quieras aparecer en Alma Conecta.
                </p>

                <div class="mt-3 space-y-1 max-h-64 overflow-y-auto rounded-lg border border-blueMid/60 bg-blueDeep/60 p-3">
                    @foreach($specialties as $specialty)
                        <label class="flex items-center gap-2 text-sm text-silver/90">
                            <input
                                type="checkbox"
                                name="specialties[]"
                                value="{{ $specialty->id }}"
                                class="rounded border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                @checked(in_array($specialty->id, $selectedSpecialties))
                            >
                            <span>{{ $specialty->name }}</span>
                        </label>
                    @endforeach
                </div>

                <x-input-error class="mt-2" :messages="$errors->get('specialties')" />
            </div>
        @endif

        {{-- Botón / estado --}}
        <div class="flex items-center gap-4">
            <x-primary-button class="bg-gold text-blueDeep hover:bg-goldStrong">
                Guardar
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-300"
                >
                    Datos actualizados.
                </p>
            @endif
        </div>
    </form>
</section>
