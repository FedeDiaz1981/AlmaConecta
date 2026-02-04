@extends('layouts.app')

@section('title', 'Panel de administración')

@section('content')
    @php
        $locked = isset($pendingEdit) && $pendingEdit;

        $currentProvinceId = old('province_id', $profile->province_id ?? '');
        $currentProvinceName = old('province_name', $profile->province_name ?? ($profile->state ?? ''));
        $currentCityId = old('city_id', $profile->city_id ?? '');
        $currentCityName = old('city_name', $profile->city_name ?? ($profile->city ?? ''));

        $currentAddress = old('address', $profile->address ?? '');
        $currentAddressExtra = old('address_extra', $profile->address_extra ?? '');

        $currentLat = old('lat', $profile->lat ?? '');
        $currentLng = old('lng', $profile->lng ?? '');

        $currentAddressStreet = old('address_street', $profile->address_street ?? '');
        $currentAddressNumber = old('address_number', $profile->address_number ?? '');

        if ($currentAddress === '' && ($currentAddressStreet !== '' || $currentAddressNumber !== '')) {
            $currentAddress = trim(implode(' ', array_filter([$currentAddressStreet, $currentAddressNumber])));
        }

        if ($currentAddressStreet === '' && $currentAddressNumber === '' && is_string($currentAddress)) {
            $addrTrim = trim($currentAddress);
            if ($addrTrim !== '' && preg_match('/^(.*?)[,]?\s+(\d[\d\w\-\/]*)$/u', $addrTrim, $m)) {
                $currentAddressStreet = trim($m[1]);
                $currentAddressNumber = trim($m[2]);
            }
        }
    @endphp

    <div class="py-10 bg-blueDeep">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6">
                <h1 class="font-semibold text-2xl text-silver">Mi perfil profesional</h1>
                <p class="mt-1 text-sm text-silver/70">Actualizá la información que van a ver tus potenciales clientes.</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                    <span class="inline-flex items-center rounded-full border border-blueMid/60 bg-blueNight/70 px-3 py-1 text-silver/80">
                        Promedio de reseñas:
                        <span class="ml-2 text-gold font-semibold">
                            {{ $reviewsCount ? $avgRating : '—' }}
                        </span>
                        <span class="ml-1 text-silver/60">/5</span>
                    </span>
                    <span class="text-silver/60 text-xs">
                        ({{ $reviewsCount ?? 0 }} {{ ($reviewsCount ?? 0) === 1 ? 'reseña' : 'reseñas' }})
                    </span>
                </div>
            </div>

            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6 sm:p-8 space-y-6">

                @if (session('status'))
                    <div
                        class="mb-2 rounded-xl border border-emerald-500/60 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-2 rounded-xl border border-red-500/60 bg-red-900/40 px-4 py-3 text-sm text-red-100">
                        <div class="font-semibold mb-1">Revisá estos campos:</div>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($locked)
                    <div
                        class="rounded-2xl border border-amber-400/70 bg-amber-900/30 px-4 py-4 text-amber-100 text-sm flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-800 text-amber-100 text-xs font-semibold">!</span>
                            <div class="font-medium">Tenés una edición pendiente de aprobación.</div>
                        </div>
                        <div>
                            Hasta que se apruebe o la anules, no podés modificar el perfil.
                            @if($pendingEdit?->created_at)
                                <span class="text-amber-200 block">
                                    Enviada el {{ $pendingEdit->created_at->format('d/m/Y H:i') }}.
                                </span>
                            @endif
                        </div>
                        <div>
                            <form method="POST" action="{{ route('dashboard.profile.cancel') }}"
                                onsubmit="return confirm('¿Anular la petición de aprobación?\nSe perderán los cambios enviados.');">
                                @csrf
                                <button
                                    class="inline-flex items-center rounded-full border border-red-400 bg-transparent px-3 py-1.5 text-xs font-medium text-red-200 hover:bg-red-900/40 transition">
                                    Anular petición
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.profile.save') }}" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf

                    {{-- BLOQUE: Datos principales --}}
                    <section class="space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">Datos principales</h3>
                                <p class="text-xs text-silver/60 mt-1">Nombre público y especialidades que verá el usuario.
                                </p>
                            </div>
                            @if($profile->status === 'approved')
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-900/60 px-3 py-1 text-xs font-medium text-emerald-200 border border-emerald-500/60">
                                    Perfil publicado
                                </span>
                            @endif
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-silver mb-1">Nombre público</label>
                                <input name="display_name"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    value="{{ old('display_name', $profile->display_name) }}" {{ $locked ? 'disabled' : '' }}>
                            </div>

                            <input type="hidden" name="service_id" value="{{ old('service_id', $profile->service_id) }}">

                            @php
                                $allSpecialties = ($specialties ?? collect())->map(fn($s) => [
                                    'id' => $s->id,
                                    'name' => $s->name,
                                ]);

                                $profileSpecialties = collect($profile->specialties ?? []);
                                $selectedIds = old('specialties', $profileSpecialties->pluck('id')->toArray());
                                $selectedIds = array_map('intval', $selectedIds);
                            @endphp

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-silver mb-1">Especialidades</label>

                                @if($allSpecialties->isEmpty())
                                    <p class="text-xs text-silver/60">Todavía no hay especialidades configuradas. Consultá con
                                        el administrador.</p>
                                @else
                                    <div id="specialty-widget" data-specialties='@json($allSpecialties)'
                                        data-locked="{{ $locked ? '1' : '0' }}" class="space-y-2">
                                        <input type="text" id="specialty-search"
                                            class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                            placeholder="Escribí para buscar especialidades..." {{ $locked ? 'disabled' : '' }}>

                                        <div id="specialty-results"
                                            class="border border-blueMid rounded-xl bg-blueDeep/95 shadow-soft mt-1 hidden max-h-52 overflow-auto text-sm divide-y divide-blueNight/60">
                                        </div>

                                        <div id="specialty-selected" class="flex flex-wrap gap-2 mt-1">
                                            @foreach($allSpecialties as $s)
                                                @if(in_array($s['id'], $selectedIds))
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 bg-gold/10 text-gold text-xs rounded-full specialty-chip border border-gold/40"
                                                        data-id="{{ $s['id'] }}">
                                                        {{ $s['name'] }}
                                                        @unless($locked)
                                                            <button type="button"
                                                                class="ml-1 text-gold hover:text-goldLight text-xs specialty-chip-remove"
                                                                aria-label="Quitar">×</button>
                                                        @endunless
                                                    </span>
                                                    <input type="hidden" name="specialties[]" value="{{ $s['id'] }}">
                                                @endif
                                            @endforeach
                                        </div>

                                        <p class="text-[11px] text-silver/60">Podés seleccionar varias especialidades.</p>
                                    </div>
                                @endif

                                @error('specialties')
                                    <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- BLOQUE: Modalidad y ubicación --}}
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">Modalidad y ubicación</h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Modalidad</label>
                                @php
                                    $currentMod = ($profile->mode_remote && $profile->mode_presential) ? 'ambas'
                                        : ($profile->mode_remote ? 'remoto' : 'presencial');
                                @endphp
                                <select name="modality"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    {{ $locked ? 'disabled' : '' }}>
                                    <option value="remoto" {{ old('modality', $currentMod) === 'remoto' ? 'selected' : '' }}>
                                        Remoto</option>
                                    <option value="ambas" {{ old('modality', $currentMod) === 'ambas' ? 'selected' : '' }}>
                                        Remoto y presencial</option>
                                    <option value="presencial" {{ old('modality', $currentMod) === 'presencial' ? 'selected' : '' }}>Presencial</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Provincia</label>
                                <select id="provinciaSelect"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    {{ $locked ? 'disabled' : '' }}>
                                    <option value="">Cargando provincias…</option>
                                </select>
                                @error('province_id')
                                    <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Ciudad</label>
                                <select id="ciudadSelect"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    disabled {{ $locked ? 'disabled' : '' }}>
                                    <option value="">Primero elegí una provincia</option>
                                </select>
                                @error('city_id')
                                    <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Direccion validada --}}
                            <style>
                                .addr-wrap {
                                    position: relative;
                                }

                                .addr-list {
                                    position: absolute;
                                    left: 0;
                                    right: 0;
                                    top: calc(100% + .35rem);
                                    z-index: 50;
                                    background: rgba(2, 6, 23, .98);
                                    border: 1px solid rgba(30, 41, 59, .9);
                                    border-radius: .9rem;
                                    box-shadow: 0 18px 35px rgba(15, 23, 42, .6);
                                    max-height: 16rem;
                                    overflow: auto;
                                }

                                .addr-item {
                                    width: 100%;
                                    text-align: left;
                                    padding: .55rem .8rem;
                                    font-size: .875rem;
                                    color: #e5e7eb;
                                }

                                .addr-item:hover {
                                    background: rgba(15, 23, 42, .95);
                                }

                                .addr-spinner {
                                    width: 12px;
                                    height: 12px;
                                    border: 2px solid rgba(226, 232, 240, .6);
                                    border-top-color: transparent;
                                    border-radius: 999px;
                                    animation: addr-spin .8s linear infinite;
                                }

                                @keyframes addr-spin {
                                    to {
                                        transform: rotate(360deg);
                                    }
                                }
                            </style>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-silver mb-1">Direccion (calle y numero)</label>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="addr-wrap">
                                        <input id="address_street" name="address_street" type="text" autocomplete="off"
                                            class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                            placeholder="Ej: Corrientes" value="{{ $currentAddressStreet }}" {{ $locked ? 'disabled' : '' }}>
                                        <div id="address_street_list" class="addr-list hidden"></div>
                                    </div>

                                    <div class="addr-wrap">
                                        <input id="address_number" name="address_number" type="text" autocomplete="off"
                                            class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                            placeholder="Ej: 1234" value="{{ $currentAddressNumber }}" {{ $locked ? 'disabled' : '' }}>
                                        <div id="address_validate_list" class="addr-list hidden"></div>
                                    </div>
                                </div>

                                <div id="street_preload" class="hidden mt-2 text-[11px] text-silver/70 flex items-center gap-2">
                                    <span class="addr-spinner"></span>
                                    Cargando calles...
                                </div>

                                <input type="hidden" name="address" id="address" value="{{ $currentAddress }}">

                                @error('address')
                                    <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                                @enderror

                                <p class="text-[11px] text-silver/60 mt-1">
                                    Escribi calle y altura. Usamos esa combinacion para calcular coordenadas. Si no se puede, tomamos el centro de la ciudad.
                                </p>
                            </div>


                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-silver mb-1">Piso / Depto (opcional)</label>
                                <input name="address_extra" id="address_extra"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    placeholder="Ej: Piso 8, Depto B" value="{{ $currentAddressExtra }}" {{ $locked ? 'disabled' : '' }}>
                                @error('address_extra')
                                    <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <p class="text-[12px] text-silver/60">
                                    Ubicación visible en tu perfil:
                                    <span id="locationPreview" class="text-silver/80 font-medium">
                                        {{ trim(implode(', ', array_filter([$currentCityName, $currentProvinceName]))) ?: '—' }}
                                    </span>
                                </p>
                            </div>

                            <input type="hidden" name="province_id" id="province_id" value="{{ $currentProvinceId }}">
                            <input type="hidden" name="province_name" id="province_name" value="{{ $currentProvinceName }}">
                            <input type="hidden" name="city_id" id="city_id" value="{{ $currentCityId }}">
                            <input type="hidden" name="city_name" id="city_name" value="{{ $currentCityName }}">

                            <input type="hidden" name="lat" id="lat" value="{{ $currentLat }}">
                            <input type="hidden" name="lng" id="lng" value="{{ $currentLng }}">
                        </div>
                    </section>

                    {{-- BLOQUE: Contacto y detalle --}}
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">Contacto y descripción</h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">WhatsApp</label>
                                <input name="whatsapp"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    placeholder="+54 9 11 5555-5555" value="{{ old('whatsapp', $profile->whatsapp) }}" {{ $locked ? 'disabled' : '' }}>
                                <p class="text-[11px] text-silver/60 mt-1">Ingresá tu número con código de país (puede
                                    llevar +, espacios o guiones).</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Correo</label>
                                <input name="contact_email" type="email"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    placeholder="tucorreo@dominio.com"
                                    value="{{ old('contact_email', $profile->contact_email) }}" {{ $locked ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-silver mb-1">Detalle (texto enriquecido)</label>
                            <link rel="stylesheet" href="https://unpkg.com/trix@2.0.4/dist/trix.css">
                            <script src="https://unpkg.com/trix@2.0.4/dist/trix.umd.min.js"></script>
                            <style>
                                /* ===== Trix en Dark UI: toolbar legible ===== */

                                /* Contenedor general */
                                trix-toolbar {
                                    background: transparent !important;
                                }

                                /* Grupos de botones */
                                trix-toolbar .trix-button-group {
                                    background: rgba(2, 6, 23, .55) !important;
                                    /* azul noche suave */
                                    border: 1px solid rgba(148, 163, 184, .35) !important;
                                    border-radius: 12px !important;
                                    overflow: hidden;
                                }

                                /* Botones */
                                trix-toolbar .trix-button {
                                    color: rgba(226, 232, 240, .95) !important;
                                    /* texto/icono claro */
                                    border: 0 !important;
                                    background: transparent !important;
                                }

                                /* Íconos (Trix usa ::before con background-image) */
                                trix-toolbar .trix-button--icon::before {
                                    filter: invert(1) brightness(1.2) contrast(1.1);
                                    opacity: .95;
                                }

                                /* Hover / Focus */
                                trix-toolbar .trix-button:hover {
                                    background: rgba(148, 163, 184, .15) !important;
                                }

                                trix-toolbar .trix-button:focus {
                                    box-shadow: 0 0 0 2px rgba(245, 158, 11, .35) !important;
                                    /* gold */
                                    outline: none !important;
                                }

                                /* Activo (seleccionado) */
                                trix-toolbar .trix-button.trix-active {
                                    background: rgba(245, 158, 11, .18) !important;
                                }

                                /* Separadores verticales */
                                trix-toolbar .trix-button-group .trix-button:not(:first-child) {
                                    border-left: 1px solid rgba(148, 163, 184, .25) !important;
                                }

                                /* Editor */
                                trix-editor {
                                    color: rgba(226, 232, 240, .95) !important;
                                    caret-color: rgba(226, 232, 240, .95) !important;
                                }

                                trix-editor:focus {
                                    border-color: rgba(245, 158, 11, .7) !important;
                                    box-shadow: 0 0 0 2px rgba(245, 158, 11, .25) !important;
                                    outline: none !important;
                                }

                                /* Placeholders */
                                trix-editor:empty:not(:focus)::before {
                                    color: rgba(148, 163, 184, .75) !important;
                                }
                            </style>

                            <input id="about" type="hidden" name="about" value="{{ old('about', $profile->about) }}" {{ $locked ? 'disabled' : '' }}>
                            <div class="rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2 shadow-sm">
                                <trix-editor input="about" class="trix-content text-sm text-silver" {{ $locked ? 'contenteditable=false' : '' }}></trix-editor>
                            </div>
                            @if($locked)
                                <p class="text-[11px] text-silver/60 mt-1">Bloqueado por solicitud pendiente.</p>
                            @endif
                        </div>
                    </section>

                    {{-- BLOQUE: Medios y template --}}
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">Imagen y formato</h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-silver mb-1">Foto</label>
                                <input type="file" name="photo" accept="image/*"
                                    class="block w-full text-sm text-silver file:mr-3 file:rounded-lg file:border-0 file:bg-gold/10 file:px-3 file:py-2 file:text-xs file:font-medium file:text-gold hover:file:bg-gold/20 disabled:opacity-60"
                                    {{ $locked ? 'disabled' : '' }}>
                                @if($profile->photo_path)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $profile->photo_path) }}"
                                            class="h-20 w-20 rounded-xl object-cover border border-blueMid">
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Video (URL)</label>
                                <input name="video_url"
                                    class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/60"
                                    placeholder="https://www.youtube.com/watch?v=..."
                                    value="{{ old('video_url', $profile->video_url) }}" {{ $locked ? 'disabled' : '' }}>
                            </div>

                            {{-- Template fijo por ahora --}}
                            <input type="hidden" name="template_key"
                                value="{{ old('template_key', $profile->template_key ?: 'a') }}">
                        </div>
                    </section>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="submit"
                            class="inline-flex items-center rounded-full bg-gold px-5 py-2.5 text-sm font-semibold text-blueDeep shadow-soft hover:bg-goldStrong focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold focus:ring-offset-blueDeep disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ $locked ? 'disabled' : '' }}>
                            Enviar a aprobación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Cascada Provincia/Ciudad (GeoRef) + Reset dirección --}}
    <script>
        (function () {
            const locked = @json($locked);
            const isLocked = !!locked;

            const provinciaSelect = document.getElementById('provinciaSelect');
            const ciudadSelect = document.getElementById('ciudadSelect');

            const provinceIdEl = document.getElementById('province_id');
            const provinceNameEl = document.getElementById('province_name');
            const cityIdEl = document.getElementById('city_id');
            const cityNameEl = document.getElementById('city_name');

            const locationPreview = document.getElementById('locationPreview');
            const streetPreloadEl = document.getElementById('street_preload');

            const addressStreetInput = document.getElementById('address_street');
            const addressNumberInput = document.getElementById('address_number');
            const addressHidden = document.getElementById('address');
            const latEl = document.getElementById('lat');
            const lngEl = document.getElementById('lng');

            const clearAddress = () => {
                if (addressStreetInput) addressStreetInput.value = '';
                if (addressNumberInput) addressNumberInput.value = '';
                if (addressHidden) addressHidden.value = '';
                if (latEl) latEl.value = '';
                if (lngEl) lngEl.value = '';
                const streetList = document.getElementById('address_street_list');
                if (streetList) { streetList.classList.add('hidden'); streetList.innerHTML = ''; }
                const validateList = document.getElementById('address_validate_list');
                if (validateList) { validateList.classList.add('hidden'); validateList.innerHTML = ''; }
                if (addressStreetInput) addressStreetInput.dataset.picked = '0';
                if (addressNumberInput) addressNumberInput.dataset.picked = '0';
            };

            if (!provinciaSelect || !ciudadSelect || !provinceIdEl || !provinceNameEl || !cityIdEl || !cityNameEl) return;

            // ✅ IMPORTANTE:
            // - resetCityUI: resetea SOLO el select (NO pisa hidden)
            // - clearCityHidden: limpia hidden (solo cuando el usuario cambia provincia)
            const resetCityUI = (placeholder = 'Primero elegí una provincia') => {
                ciudadSelect.innerHTML = `<option value="">${placeholder}</option>`;
                ciudadSelect.disabled = true;
            };

            const clearCityHidden = () => {
                cityIdEl.value = '';
                cityNameEl.value = '';
            };

            const syncPreview = () => {
                if (!locationPreview) return;
                const label = [cityNameEl.value, provinceNameEl.value].filter(Boolean).join(', ');
                locationPreview.textContent = label || (provinceNameEl.value ? `—, ${provinceNameEl.value}` : '—');
            };

            const normalizeText = (value) => {
                return (value || '')
                    .toString()
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            };

            const setStreetPreload = (on) => {
                if (!streetPreloadEl) return;
                streetPreloadEl.classList.toggle('hidden', !on);
            };

            const preloadStreets = async () => {
                if (isLocked) return;
                const cityName = (cityNameEl.value || '').trim();
                const provinceName = (provinceNameEl.value || '').trim();

                if (!cityName || !provinceName) {
                    setStreetPreload(false);
                    return;
                }

                setStreetPreload(true);
                if (addressStreetInput) {
                    addressStreetInput.disabled = true;
                    addressStreetInput.classList.add('opacity-60', 'cursor-wait');
                }

                try {
                    const url = new URL('/geo/street-preload', window.location.origin);
                    url.searchParams.set('city_name', cityName);
                    url.searchParams.set('province_name', provinceName);
                    await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                } catch (e) {
                    // noop
                } finally {
                    setTimeout(() => setStreetPreload(false), 300);
                    if (addressStreetInput) {
                        addressStreetInput.disabled = false;
                        addressStreetInput.classList.remove('opacity-60', 'cursor-wait');
                    }
                }
            };

            const fillSelect = (select, items, placeholder) => {
                select.innerHTML = `<option value="">${placeholder}</option>`;
                for (const it of items) {
                    const opt = document.createElement('option');
                    opt.value = it.id;
                    opt.textContent = it.nombre;
                    select.appendChild(opt);
                }
            };

            const loadProvinces = async () => {
                try {
                    const res = await fetch('/geo/provincias', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error('No OK');
                    const data = await res.json();
                    const items = Array.isArray(data.items) ? data.items : [];

                    fillSelect(provinciaSelect, items, 'Seleccioná una provincia');

                    const prevProvinceId = (provinceIdEl.value || '').trim();
                    const prevProvinceName = (provinceNameEl.value || '').trim();
                    if (prevProvinceId) {
                        provinciaSelect.value = prevProvinceId;

                        const opt = provinciaSelect.selectedOptions?.[0];
                        if (opt) provinceNameEl.value = opt.textContent || provinceNameEl.value || '';

                        // ✅ restaurar ciudades sin pisar hidden en el init
                        await loadCitiesForProvince(prevProvinceId, true);
                    } else if (prevProvinceName) {
                        const match = Array.from(provinciaSelect.options).find((opt) =>
                            normalizeText(opt.textContent) === normalizeText(prevProvinceName)
                        );

                        if (match) {
                            provinciaSelect.value = match.value;
                            provinceIdEl.value = match.value;
                            provinceNameEl.value = match.textContent || prevProvinceName;
                            await loadCitiesForProvince(match.value, true);
                        } else {
                            resetCityUI('Primero elegí una provincia');
                        }
                    } else {
                        resetCityUI('Primero elegí una provincia');
                    }

                    syncPreview();
                    if ((cityNameEl.value || '').trim() && (provinceNameEl.value || '').trim()) {
                        preloadStreets();
                    }
                } catch (e) {
                    provinciaSelect.innerHTML = `<option value="">No se pudieron cargar provincias</option>`;
                    resetCityUI('No se pudieron cargar ciudades');
                    syncPreview();
                }
            };

            const loadCitiesForProvince = async (provinceId, tryRestoreFromHidden = false) => {
                // UI loading, sin tocar hidden
                resetCityUI('Cargando ciudades…');

                if (!provinceId) {
                    resetCityUI();
                    return;
                }

                try {
                    const url = new URL('/geo/ciudades', window.location.origin);
                    url.searchParams.set('provincia', provinceId);

                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error('No OK');

                    const data = await res.json();
                    const items = Array.isArray(data.items) ? data.items : [];

                    ciudadSelect.disabled = isLocked ? true : false;
                    fillSelect(ciudadSelect, items, 'Seleccioná una ciudad');

                    if (tryRestoreFromHidden) {
                        const prevCityId = (cityIdEl.value || '').trim();
                        const prevCityName = (cityNameEl.value || '').trim();
                        if (prevCityId) {
                            ciudadSelect.value = prevCityId;
                            const opt = ciudadSelect.selectedOptions?.[0];

                            // si no existe en el select, limpiamos hidden (evita inconsistencias)
                            if (ciudadSelect.value !== prevCityId) {
                                clearCityHidden();
                            } else if (opt) {
                                cityNameEl.value = opt.textContent || cityNameEl.value || '';
                            }
                        } else if (prevCityName) {
                            const match = Array.from(ciudadSelect.options).find((opt) =>
                                normalizeText(opt.textContent) === normalizeText(prevCityName)
                            );

                            if (match) {
                                ciudadSelect.value = match.value;
                                cityIdEl.value = match.value;
                                cityNameEl.value = match.textContent || prevCityName;
                            } else {
                                clearCityHidden();
                            }
                        }
                    }

                    syncPreview();
                } catch (e) {
                    resetCityUI('No se pudieron cargar ciudades');
                    syncPreview();
                }
            };

            if (!isLocked) {
                provinciaSelect.addEventListener('change', async () => {
                    const provinceId = provinciaSelect.value || '';
                    const provinceName = provinciaSelect.selectedOptions?.[0]?.textContent || '';

                    provinceIdEl.value = provinceId;
                    provinceNameEl.value = provinceName;

                    // ✅ ahora sí: al cambiar provincia (acción del usuario) limpiamos ciudad + dirección
                    clearCityHidden();
                    resetCityUI('Seleccioná una ciudad');
                    clearAddress();

                    await loadCitiesForProvince(provinceId, false);
                    syncPreview();
                });

                ciudadSelect.addEventListener('change', () => {
                    const cityId = ciudadSelect.value || '';
                    const cityName = ciudadSelect.selectedOptions?.[0]?.textContent || '';

                    cityIdEl.value = cityId;
                    cityNameEl.value = cityName;

                    // ✅ al cambiar ciudad: dirección no es válida
                    clearAddress();
                    syncPreview();
                    preloadStreets();
                });
            }

            // init
            // ✅ NO limpiamos hidden en init (ese era el bug que te borraba city/address)
            resetCityUI('Cargando ciudades…');
            syncPreview();
            loadProvinces();
        })();
    </script>

    {{-- Autocomplete Direccion: calle + altura --}}
    <script>
        (function () {
            const locked = @json($locked);
            if (locked) return;

            const streetInput = document.getElementById('address_street');
            const numberInput = document.getElementById('address_number');
            const addressHidden = document.getElementById('address');
            const streetList = document.getElementById('address_street_list');
            const validateList = document.getElementById('address_validate_list');

            const cityNameEl = document.getElementById('city_name');
            const provinceNameEl = document.getElementById('province_name');

            const latEl = document.getElementById('lat');
            const lngEl = document.getElementById('lng');

            const ciudadSelect = document.getElementById('ciudadSelect');

            if (!streetInput || !numberInput || !addressHidden || !streetList || !validateList || !cityNameEl || !provinceNameEl || !latEl || !lngEl) return;

            let tStreet = null;
            let tValidate = null;
            const debounceStreet = (fn, ms = 260) => { clearTimeout(tStreet); tStreet = setTimeout(fn, ms); };
            const debounceValidate = (fn, ms = 260) => { clearTimeout(tValidate); tValidate = setTimeout(fn, ms); };

            const hideList = (list) => { list.classList.add('hidden'); list.innerHTML = ''; };

            const clearValidation = () => {
                latEl.value = '';
                lngEl.value = '';
                numberInput.dataset.picked = '0';
            };

            const syncAddressHidden = () => {
                const street = (streetInput.value || '').trim();
                const number = (numberInput.value || '').trim();
                const composed = [street, number].filter(Boolean).join(' ').trim();
                addressHidden.value = composed;
            };

            const setStreetPicked = (label) => {
                streetInput.dataset.picked = '1';
                streetInput.dataset.chosen = label;
            };

            const clearStreetPicked = () => {
                streetInput.dataset.picked = '0';
                streetInput.dataset.chosen = '';
            };

            const setNumberEnabled = () => {
                const hasStreet = (streetInput.value || '').trim().length > 0;
                numberInput.disabled = !hasStreet;
                numberInput.classList.toggle('opacity-60', !hasStreet);
                numberInput.classList.toggle('cursor-not-allowed', !hasStreet);
                if (!hasStreet) {
                    numberInput.value = '';
                    clearValidation();
                    hideList(validateList);
                }
                syncAddressHidden();
            };

            const setEnabledByCity = () => {
                const hasCity = (cityNameEl.value || '').trim().length > 0;
                streetInput.disabled = !hasCity;
                streetInput.classList.toggle('opacity-60', !hasCity);
                streetInput.classList.toggle('cursor-not-allowed', !hasCity);

                if (!hasCity) {
                    streetInput.value = '';
                    numberInput.value = '';
                    clearStreetPicked();
                    clearValidation();
                    hideList(streetList);
                    hideList(validateList);
                }

                setNumberEnabled();
            };

            const escapeHtml = (s) =>
                String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');

            const renderStreet = (items) => {
                if (!items || !items.length) return hideList(streetList);

                streetList.innerHTML = items.map(it => `
                        <button type="button"
                                class="addr-item street-item"
                                data-label="${escapeHtml(it.label)}">
                            ${escapeHtml(it.label)}
                        </button>
                    `).join('');

                streetList.classList.remove('hidden');
            };

            const renderValidate = (items) => {
                if (!items || !items.length) return hideList(validateList);

                validateList.innerHTML = items.map(it => `
                        <button type="button"
                                class="addr-item address-item"
                                data-label="${escapeHtml(it.label)}"
                                data-lat="${it.lat}"
                                data-lng="${it.lng}">
                            ${escapeHtml(it.label)}
                        </button>
                    `).join('');

                validateList.classList.remove('hidden');
            };

            const fetchStreetSuggest = async () => {
                const q = (streetInput.value || '').trim();
                const cityName = (cityNameEl.value || '').trim();
                const provinceName = (provinceNameEl.value || '').trim();

                if (q.length < 2 || !cityName || !provinceName) {
                    hideList(streetList);
                    return;
                }

                try {
                    const url = new URL('/geo/street-suggest', window.location.origin);
                    url.searchParams.set('q', q);
                    url.searchParams.set('city_name', cityName);
                    url.searchParams.set('province_name', provinceName);

                    const r = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) return hideList(streetList);

                    const j = await r.json();
                    renderStreet(Array.isArray(j.items) ? j.items : []);
                } catch {
                    hideList(streetList);
                }
            };

            const shouldValidateNow = (street, number) => {
                return street.length >= 3 && number.length >= 1 && /\d/.test(number);
            };

            const fetchValidateSuggest = async () => {
                const street = (streetInput.value || '').trim();
                const number = (numberInput.value || '').trim();
                const cityName = (cityNameEl.value || '').trim();
                const provinceName = (provinceNameEl.value || '').trim();

                if (!street || !number || !cityName || !provinceName) {
                    hideList(validateList);
                    return;
                }

                if (!shouldValidateNow(street, number)) {
                    hideList(validateList);
                    return;
                }

                try {
                    const q = `${street} ${number}`.trim();
                    if ((addressHidden.value || '').trim() !== q) {
                        clearValidation();
                    }

                    const url = new URL('/geo/address-suggest', window.location.origin);
                    url.searchParams.set('q', q);
                    url.searchParams.set('city_name', cityName);
                    url.searchParams.set('province_name', provinceName);

                    const r = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) return hideList(validateList);

                    const j = await r.json();
                    renderValidate(Array.isArray(j.items) ? j.items : []);
                } catch {
                    hideList(validateList);
                }
            };

            streetInput.addEventListener('input', () => {
                if (streetInput.disabled) return;
                const chosen = (streetInput.dataset.chosen || '').trim();
                if (streetInput.dataset.picked === '1' && streetInput.value.trim() !== chosen) {
                    clearStreetPicked();
                }
                clearValidation();
                setNumberEnabled();
                debounceStreet(fetchStreetSuggest, 280);
            });

            streetList.addEventListener('click', (e) => {
                const btn = e.target.closest('.street-item');
                if (!btn) return;

                const label = (btn.dataset.label || '').trim();
                streetInput.value = label;
                setStreetPicked(label);
                hideList(streetList);
                clearValidation();
                setNumberEnabled();
                numberInput.focus();
            });

            streetInput.addEventListener('blur', () => {
                setTimeout(() => {
                    const typed = (streetInput.value || '').trim();
                    const chosen = (streetInput.dataset.chosen || '').trim();

                    if (!typed) {
                        clearStreetPicked();
                        clearValidation();
                        hideList(streetList);
                        setNumberEnabled();
                        return;
                    }

                    if (streetInput.dataset.picked === '1' && typed !== chosen) {
                        clearStreetPicked();
                    }

                    hideList(streetList);
                }, 160);
            });

            numberInput.addEventListener('input', () => {
                if (numberInput.disabled) return;
                numberInput.dataset.picked = '0';
                clearValidation();
                syncAddressHidden();
                debounceValidate(fetchValidateSuggest, 280);
            });

            validateList.addEventListener('click', (e) => {
                const btn = e.target.closest('.address-item');
                if (!btn) return;

                const label = (btn.dataset.label || '').trim();
                const lat = btn.dataset.lat;
                const lng = btn.dataset.lng;

                addressHidden.value = label;
                if (lat) latEl.value = Number(lat).toFixed(7);
                if (lng) lngEl.value = Number(lng).toFixed(7);
                numberInput.dataset.picked = '1';

                const match = label.match(/^(.*?)[,]?\s+(\d[\d\w\-\/]*)$/);
                if (match) {
                    streetInput.value = match[1].trim();
                    numberInput.value = match[2].trim();
                    setStreetPicked(match[1].trim());
                }

                hideList(validateList);
            });

            numberInput.addEventListener('blur', () => {
                setTimeout(() => {
                    const typed = (numberInput.value || '').trim();

                    if (!typed) {
                        clearValidation();
                        hideList(validateList);
                        syncAddressHidden();
                        return;
                    }

                    hideList(validateList);
                }, 160);
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#address_street_list') && e.target !== streetInput) {
                    hideList(streetList);
                }
                if (!e.target.closest('#address_validate_list') && e.target !== numberInput) {
                    hideList(validateList);
                }
            });

            if (ciudadSelect) ciudadSelect.addEventListener('change', () => setEnabledByCity());

            // init
            if ((streetInput.value || '').trim()) {
                setStreetPicked((streetInput.value || '').trim());
            } else {
                clearStreetPicked();
            }

            if ((addressHidden.value || '').trim() && (numberInput.value || '').trim()) {
                numberInput.dataset.picked = '1';
            } else {
                numberInput.dataset.picked = '0';
            }

            setEnabledByCity();
        })();
    </script>

    {{-- Widget especialidades --}}
    <script>
        (function () {
            const widget = document.getElementById('specialty-widget');
            if (!widget) return;

            const locked = widget.dataset.locked === '1';
            if (locked) return;

            const specialties = JSON.parse(widget.dataset.specialties || '[]');
            const input = document.getElementById('specialty-search');
            const results = document.getElementById('specialty-results');
            const selectedWrap = document.getElementById('specialty-selected');

            if (!input || !results || !selectedWrap) return;

            let currentQuery = '';

            function getSelectedIds() {
                return Array.from(
                    selectedWrap.querySelectorAll('input[name="specialties[]"]')
                ).map(el => parseInt(el.value, 10));
            }

            function addSpecialty(id, name) {
                const current = getSelectedIds();
                if (current.includes(id)) return;

                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center px-2.5 py-1 bg-gold/10 text-gold text-xs rounded-full specialty-chip border border-gold/40';
                chip.dataset.id = id;
                chip.innerHTML = `
                    ${name}
                    <button type="button"
                            class="ml-1 text-gold hover:text-goldLight text-xs specialty-chip-remove"
                            aria-label="Quitar">×</button>
                `;
                selectedWrap.appendChild(chip);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'specialties[]';
                hidden.value = id;
                selectedWrap.appendChild(hidden);
            }

            function removeSpecialty(id) {
                Array.from(selectedWrap.querySelectorAll('.specialty-chip')).forEach(chip => {
                    if (parseInt(chip.dataset.id, 10) === id) chip.remove();
                });
                Array.from(selectedWrap.querySelectorAll('input[name="specialties[]"]')).forEach(inp => {
                    if (parseInt(inp.value, 10) === id) inp.remove();
                });
            }

            function renderResults(list) {
                if (!list.length) {
                    results.classList.add('hidden');
                    results.innerHTML = '';
                    return;
                }

                results.innerHTML = list.map(s => `
                    <button type="button"
                            class="w-full text-left px-3 py-1.5 text-sm hover:bg-blueNight/70 text-silver specialty-result-item"
                            data-id="${s.id}"
                            data-name="${s.name}">
                        ${s.name}
                    </button>
                `).join('');

                results.classList.remove('hidden');
            }

            function refreshResults() {
                const q = currentQuery.trim();
                if (!q) {
                    results.classList.add('hidden');
                    results.innerHTML = '';
                    return;
                }

                const qLower = q.toLowerCase();
                const selected = getSelectedIds();
                const filtered = specialties.filter(s =>
                    s.name.toLowerCase().includes(qLower) &&
                    !selected.includes(s.id)
                );

                renderResults(filtered);
            }

            let t = null;
            input.addEventListener('input', function () {
                currentQuery = this.value;
                clearTimeout(t);
                t = setTimeout(refreshResults, 150);
            });

            results.addEventListener('click', function (e) {
                const btn = e.target.closest('.specialty-result-item');
                if (!btn) return;

                const id = parseInt(btn.dataset.id, 10);
                const name = btn.dataset.name;

                addSpecialty(id, name);
                refreshResults();
            });

            selectedWrap.addEventListener('click', function (e) {
                const btn = e.target.closest('.specialty-chip-remove');
                if (!btn) return;

                const chip = btn.closest('.specialty-chip');
                if (!chip) return;

                const id = parseInt(chip.dataset.id, 10);
                removeSpecialty(id);
                refreshResults();
            });
        })();
    </script>
@endsection
