@extends('layouts.app')

@section('title', 'Alma Conecta - Bienestar holístico')

@section('content')

@php
    $heroMobile = asset('hero_lma_conecta_Mobile.png');
    $heroDesktop = asset('hero_lma_conecta.png');
@endphp

<style>
    .hero-bg {
        background-size: cover;
        background-position: top;
        background-image: url('{{ $heroMobile }}'); /* MOBILE */
    }

    @media (min-width: 768px) {
        .hero-bg {
            background-image: url('{{ $heroDesktop }}'); /* DESKTOP */
        }
    }
</style>

{{-- ============================= --}}
{{-- HERO / PORTADA (con imagen IA de fondo) --}}
{{-- ============================= --}}
<section
    class="relative w-full min-h-[80vh] md:min-h-[95vh] text-silver bg-cover bg-top overflow-hidden hero-bg">
    {{-- Capa oscura para que se lea el texto --}}
    <div class="absolute inset-0 bg-black/40"></div>

    {{-- Luz dorada encima (tu gradiente) --}}
    <div class="absolute inset-0 opacity-50"
         style="background: radial-gradient(circle at 25% 15%, rgba(203,160,67,0.45), transparent 55%);">
    </div>

    {{-- CONTENIDO --}}
    <div class="relative max-w-8xl mx-auto md:ml-[3%] px-0 w-full pt-20 md:pt-10 pb-12">
        <div class="w-full md:max-w-8xl mx-auto md:mx-0 text-center md:text-left">

            <h1 class="text-3xl md:text-[2.4rem] font-bold leading-tight mb-4">
                Encontrá tu espacio de <span class="text-gold">bienestar holístico</span>
            </h1>

            <p class="text-silver/80 text-base md:text-lg mb-10">
                Conectá con terapeutas, facilitadores y espacios de bienestar en un solo lugar.
            </p>

            {{-- Buscador principal --}}
            <form method="GET" action="{{ route('search') }}"
                class="hero-search bg-blueInk/80 border border-blueNight rounded-2xl p-5 shadow-soft backdrop-blur-md
                        mx-auto md:mx-0">

                <div class="flex flex-col gap-4">

                    {{-- q: texto libre --}}
                    <div class="flex flex-col relative">
                        <label class="text-[14px] font-semibold tracking-wide uppercase text-silver/60 mb-1 text-left">
                            ¿Qué estás buscando? <span class="text-red-400">*</span>
                        </label>

                        <div class="relative">
                            <input type="text"
                                name="q"
                                id="q"
                                autocomplete="off"
                                required
                                placeholder="Reiki, Yoga, Constelaciones..."
                                value="{{ request('q') }}"
                                class="hero-input pr-9">

                            <button type="button" id="q-clear"
                                    class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-silver/60 hover:text-silver text-xs">
                                ✕
                            </button>
                        </div>

                        {{-- Eliminado: specialty_id + dropdown de sugerencias --}}
                    </div>

                    {{-- loc --}}
                    <div class="flex flex-col relative">
                        <label class="text-[14px] font-semibold tracking-wide uppercase text-silver/60 mb-1 text-left">
                            ¿Dónde? <span class="text-red-400">*</span>
                        </label>

                        <div class="relative">
                            <input type="text"
                                name="loc"
                                id="loc"
                                autocomplete="off"
                                required
                                placeholder="Ciudad o barrio"
                                value="{{ request('loc') }}"
                                class="hero-input pr-9">

                            <button type="button" id="loc-clear"
                                    class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-silver/60 hover:text-silver text-xs">
                                ✕
                            </button>
                        </div>

                        <input type="hidden" id="lat" name="lat" value="{{ request('lat') }}">
                        <input type="hidden" id="lng" name="lng" value="{{ request('lng') }}">

                        <div id="loc-suggestions"
                            class="absolute left-0 right-0 top-full mt-1 bg-blueNight border border-blueMid rounded-xl shadow-soft
                                    max-h-56 overflow-auto text-sm hidden z-20">
                        </div>
                    </div>

                    {{-- radio + remoto --}}
                    <div class="flex flex-col gap-2">
                        <div class="flex flex-col">
                            <label class="text-[14px] font-semibold tracking-wide uppercase text-silver/60 mb-1 text-left">
                                Área de búsqueda
                            </label>

                            <select name="r" class="hero-input">
                                @foreach([5, 10, 20, 50, 100] as $radius)
                                    <option value="{{ $radius }}" {{ (int)request('r', 20) === $radius ? 'selected' : '' }}>
                                        Hasta {{ $radius }} km
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="flex items-center gap-2 text-[14px] text-silver/70 text-left">
                            <input type="checkbox"
                                name="remote"
                                value="1"
                                class="h-4 w-4 rounded border-blueNight bg-blueNight/70 text-gold focus:ring-gold"
                                {{ request()->boolean('remote', true) ? 'checked' : '' }}>
                            <span class="text-[14px]">Incluir modalidad online/remota</span>
                        </label>

                        <button type="submit"
                                id="search-btn"
                                aria-disabled="true"
                                class="hero-button opacity-50 cursor-not-allowed mx-auto w-1/2">
                            Buscar
                        </button>

                    </div>
                </div>

            </form>

            <p class="mt-4 text-[16px] text-silver/60">
                Tip: podés buscar directamente por especialidad.
            </p>
        </div>
    </div>
</section>



{{-- ============================= --}}
{{-- BLOQUE APP / BENEFICIOS --}}
{{-- ============================= --}}
<section class="bg-blueNight py-12 md:py-20">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16 items-center">

        <div class="flex justify-center md:justify-start">
            <h2 class="text-3xl font-semibold text-silver mb-3">
                Si sos un un <span class="text-gold text-4xl">profesional</span>  y querés <span class="text-gold text-4xl">cargar</span> tu perfil hacé click <span class="text-gold text-4xl">acá</span>.
            </h2>
        </div>

        <div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.profile.edit') }}"
                   class="px-5 py-2.5 rounded-full border border-gold text-gold text-xl font-semibold hover:bg-gold/10 transition mx-auto">
                    Registrarme
                </a>
            </div>
        </div>
    </div>
</section>


{{-- ============================= --}}
{{-- PRÁCTICAS MÁS BUSCADAS        --}}
{{-- ============================= --}}
<section class="bg-blueDeep py-12 md:py-[8rem] md:min-h-[20rem]">
    <div class="max-w-[76rem] mx-auto px-6">
       
        @if(isset($topSpecialties) && $topSpecialties->count())
            <div class="relative">

        {{-- Flecha izquierda --}}
            <button id="specialty-prev"
                    type="button"
                    class="hidden md:flex absolute left-[-7.5rem] top-1/2 -translate-y-1/2
                        h-20 w-20 rounded-full bg-blueNight/95
                        text-gold shadow-soft
                        items-center justify-center transition group">
                <span class="inline-flex items-center justify-center text-[4rem] leading-none
                            transition-transform duration-200 group-hover:scale-125">
                    ‹
                </span>
            </button>

        {{-- Flecha derecha --}}
            <button id="specialty-next"
                    type="button"
                    class="hidden md:flex absolute right-[-7rem] top-1/2 -translate-y-1/2
                        h-20 w-20 rounded-full bg-blueNight/95
                        text-gold shadow-soft
                        items-center justify-center transition group">
                <span class="inline-flex items-center justify-center text-[4rem] leading-none
                            transition-transform duration-200 group-hover:scale-125">
                    ›
                </span>
            </button>



                <style>
                    #specialty-carousel::-webkit-scrollbar { display: none; }
                </style>

                <div id="specialty-carousel"
                     class="flex gap-5 overflow-y-visible overflow-x-auto md:overflow-x-visible scroll-smooth snap-x snap-mandatory pb-2
                            [-ms-overflow-style:'none'] [scrollbar-width:'none']">

                    @foreach($topSpecialties as $specialty)
                        <a href="{{ route('search', ['q' => $specialty->name]) }}"
                        class="snap-start shrink-0 relative group
                                min-w-[260px] max-w-[80vw] md:w-[370px]
                                h-[320px] md:h-[480px]
                                rounded-3xl overflow-hidden
                                border border-blueMid/70 bg-blueNight/80
                                transition-transform duration-300 ease-out
                                hover:-translate-y-1 hover:scale-[1.03] hover:shadow-strong hover:border-gold/80">

                            @if($specialty->featured_image_path)
                                <img
                                    src="{{ asset('storage/'.$specialty->featured_image_path) }}"
                                    alt="{{ $specialty->name }}"
                                    class="absolute inset-0 w-full h-full object-cover"
                                >
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-blueNight via-blueDeep to-blueInk"></div>
                            @endif

                            <div class="absolute inset-0
                                        bg-gradient-to-b from-black/30 via-black/35 to-black/60
                                        group-hover:from-black/25 group-hover:via-black/30 group-hover:to-black/55
                                        transition-colors duration-300">
                            </div>

                            <div class="relative z-10 flex flex-col justify-between h-full p-4 md:p-5">
                                <div class="flex items-center gap-2">
                                    <span class="px-4 py-1.5 rounded-full
                                                text-[11px] md:text-xs font-semibold tracking-[0.20em]
                                                uppercase bg-gold text-blueDeep
                                                border border-gold/90
                                                shadow-[0_0_14px_rgba(250,204,21,0.7)]">
                                        ACTIVIDAD DESTACADA
                                    </span>
                                </div>

                                <div class="flex-1 flex items-center justify-center px-2">
                                    <h3 class="inline-block text-center text-base md:text-2xl font-semibold text-silver leading-snug
                                            bg-blueNight/85 px-4 py-3 rounded-2xl shadow-soft">
                                        {{ $specialty->name }}
                                    </h3>
                                </div>
                            </div>
                        </a>
                    @endforeach

                </div>
            </div>
        @else
            <p class="text-silver/60 text-sm">
                Todavía no hay prácticas destacadas cargadas.
            </p>
        @endif
    </div>
</section>




{{-- ============================= --}}
{{-- FACILITADORES DESTACADOS --}}
{{-- ============================= --}}
<section class="bg-blueNight py-12 md:py-16">
    <div class="max-w-6xl mx-auto px-6">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-semibold text-silver flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-gold/60 text-gold text-xs">
                    ★
                </span>
                <span>Facilitadores destacados</span>
            </h2>
            <a href="{{ route('search') }}" class="text-sm text-gold hover:text-goldLight">
                Ver más
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(($featuredProfiles ?? []) as $profile)
                <article
                    class="group relative bg-gradient-to-b from-blueDeep/95 via-blueDeep/90 to-blueNight/95
                           p-5 rounded-2xl shadow-soft border border-blueInk/60
                           transition-transform duration-300 ease-out
                           hover:-translate-y-1 hover:shadow-strong hover:border-gold/50">

                    <div class="absolute right-4 top-4 text-[10px] uppercase tracking-[0.14em]
                                px-2 py-1 rounded-full bg-gold/10 text-gold border border-gold/40">
                        Destacado
                    </div>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="relative h-12 w-12 rounded-full bg-blueNight overflow-hidden flex items-center justify-center text-xs font-semibold text-gold/80 border border-gold/40">
                            @if(!empty($profile->avatar_path))
                                <img
                                    src="{{ asset('storage/'.$profile->avatar_path) }}"
                                    alt="{{ $profile->display_name }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                @php
                                    $initials = collect(explode(' ', $profile->display_name))
                                        ->filter()
                                        ->map(fn($p) => mb_substr($p, 0, 1))
                                        ->take(2)
                                        ->join('');
                                @endphp
                                <span>{{ $initials }}</span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h3 class="text-silver font-semibold text-sm truncate">
                                {{ $profile->display_name }}
                            </h3>
                            <p class="text-silver/70 text-[11px] truncate">
                                {{ $profile->specialties->pluck('name')->take(2)->join(' · ') }}
                            </p>
                        </div>
                    </div>

                    <p class="text-silver/80 text-xs mb-4 line-clamp-3">
                        {{ Str::limit(strip_tags($profile->about), 160) }}
                    </p>

                    <div class="flex items-center justify-between text-[11px] text-silver/60">
                        <span class="truncate">
                            {{ $profile->city }}, {{ $profile->state }}
                        </span>
                        <a href="{{ route('profiles.show', $profile->slug) }}"
                           class="font-semibold text-gold hover:text-goldLight text-[11px] group-hover:underline">
                            Ver perfil
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
{{-- MODAL VALIDACIÓN --}}
<div id="validation-modal"
     class="fixed inset-0 hidden z-50">
    <div class="absolute inset-0 bg-black/60"></div>

    <div class="relative mx-auto mt-24 w-[92%] max-w-md rounded-2xl bg-blueNight border border-blueMid shadow-strong p-5">
        <h3 class="text-silver font-semibold text-lg mb-2">
            Falta completar datos
        </h3>

        <p id="validation-modal-msg" class="text-silver/80 text-sm mb-4">
            —
        </p>

        <div class="flex justify-end gap-2">
            <button type="button"
                    id="validation-modal-close"
                    class="px-4 py-2 rounded-xl border border-blueMid text-silver hover:bg-blueMid/30">
                Cerrar
            </button>
        </div>
    </div>

{{-- ============================= --}}
{{-- JS: Ubicación + carrusel --}}
{{-- ============================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.getElementById('search-btn');
    const form = document.querySelector('form[action="{{ route('search') }}"]');

    // Inputs
    const qInput  = document.getElementById('q');
    const qClear  = document.getElementById('q-clear');

    const locInput = document.getElementById('loc');
    const latEl    = document.getElementById('lat');
    const lngEl    = document.getElementById('lng');
    const locBox   = document.getElementById('loc-suggestions');
    const locClear = document.getElementById('loc-clear');

    // Modal
    const modal      = document.getElementById('validation-modal');
    const modalMsg   = document.getElementById('validation-modal-msg');
    const modalClose = document.getElementById('validation-modal-close');
    const modalGo    = document.getElementById('validation-modal-go');

    let lastMissingField = null;

    const showModal = (msg, fieldEl) => {
        if (!modal || !modalMsg) return;
        lastMissingField = fieldEl || null;
        modalMsg.textContent = msg;
        modal.classList.remove('hidden');
    };

    const hideModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        lastMissingField = null;
    };

    if (modalClose) modalClose.addEventListener('click', hideModal);
    if (modalGo) {
        modalGo.addEventListener('click', () => {
            hideModal();
            if (lastMissingField) lastMissingField.focus();
        });
    }
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) hideModal();
        });
    }

    const getMissing = () => {
        if (!qInput || qInput.value.trim() === '') {
            return { msg: 'Completá “¿Qué estás buscando?”.', field: qInput };
        }
        if (!locInput || locInput.value.trim() === '') {
            return { msg: 'Completá “¿Dónde?”.', field: locInput };
        }
        if (!latEl?.value || !lngEl?.value) {
            return { msg: 'Elegí una ubicación de las sugerencias para fijar coordenadas.', field: locInput };
        }
        return null;
    };

    const setPseudoDisabled = (isDisabled) => {
        if (!searchBtn) return;
        searchBtn.setAttribute('aria-disabled', isDisabled ? 'true' : 'false');
        searchBtn.classList.toggle('opacity-50', isDisabled);
        searchBtn.classList.toggle('cursor-not-allowed', isDisabled);
    };

    const updateSearchButtonState = () => {
        setPseudoDisabled(!!getMissing());
    };

    // CLICK en Buscar (flujo central)
    if (searchBtn) {
        searchBtn.addEventListener('click', (e) => {
            const missing = getMissing();
            if (missing) {
                e.preventDefault();
                showModal(missing.msg, missing.field);
            }
        });
    }

    // SUBMIT de seguridad (por si algo dispara submit)
    if (form) {
        form.addEventListener('submit', (e) => {
            const missing = getMissing();
            if (missing) {
                e.preventDefault();
                showModal(missing.msg, missing.field);
            }
        });
    }

    // ===== ENTER → CLICK EN BUSCAR =====
    [qInput, locInput].forEach(input => {
        if (!input) return;
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();          // evita submit automático
                if (searchBtn) searchBtn.click(); // MISMO flujo que click
            }
        });
    });

    // ----- Q (texto libre) -----
    if (qInput) {
        const syncQClear = () => {
            if (!qClear) return;
            qClear.classList.toggle('hidden', qInput.value.trim() === '');
        };

        qInput.addEventListener('input', () => {
            syncQClear();
            updateSearchButtonState();
        });

        if (qClear) {
            qClear.addEventListener('click', () => {
                qInput.value = '';
                syncQClear();
                updateSearchButtonState();
                qInput.focus();
            });
        }

        syncQClear();
    }

    // ----- AUTOCOMPLETE UBICACIÓN -----
    if (locInput && latEl && lngEl && locBox) {
        let locTimeout = null;

        const hideLocBox = () => {
            locBox.classList.add('hidden');
            locBox.innerHTML = '';
        };

        const lockLocInput = () => {
            locInput.readOnly = true;
            locInput.classList.add('cursor-default');
            if (locClear) locClear.classList.remove('hidden');
            updateSearchButtonState();
        };

        const unlockLocInput = () => {
            locInput.readOnly = false;
            locInput.value = '';
            latEl.value = '';
            lngEl.value = '';
            locInput.classList.remove('cursor-default');
            if (locClear) locClear.classList.add('hidden');
            updateSearchButtonState();
        };

        const showLocSuggestions = (items) => {
            if (!items.length) {
                hideLocBox();
                return;
            }

            locBox.innerHTML = '';
            items.forEach(i => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-3 py-2 hover:bg-blueMid/60 text-silver text-sm';
                btn.dataset.lat = i.lat;
                btn.dataset.lng = i.lon;
                btn.textContent = i.display_name;

                btn.addEventListener('click', () => {
                    locInput.value = i.display_name;
                    latEl.value = i.lat;
                    lngEl.value = i.lon;
                    hideLocBox();
                    lockLocInput();
                    updateSearchButtonState();
                });

                locBox.appendChild(btn);
            });

            locBox.classList.remove('hidden');
        };

        const fetchLocSuggestions = async (q) => {
            latEl.value = '';
            lngEl.value = '';
            if (q.length < 3 || locInput.readOnly) {
                hideLocBox();
                updateSearchButtonState();
                return;
            }

            try {
                const url = new URL('https://nominatim.openstreetmap.org/search');
                url.searchParams.set('q', q);
                url.searchParams.set('format', 'json');
                url.searchParams.set('limit', '6');
                url.searchParams.set('accept-language', 'es');
                url.searchParams.set('countrycodes', 'ar');

                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return hideLocBox();
                const data = await res.json();
                showLocSuggestions(Array.isArray(data) ? data : []);
            } catch {
                hideLocBox();
            } finally {
                updateSearchButtonState();
            }
        };

        locInput.addEventListener('input', () => {
            if (locInput.readOnly) return;
            clearTimeout(locTimeout);
            locTimeout = setTimeout(() => fetchLocSuggestions(locInput.value.trim()), 350);
            updateSearchButtonState();
        });

        locInput.addEventListener('blur', () => {
            setTimeout(() => {
                hideLocBox();
                if (!latEl.value || !lngEl.value) locInput.value = '';
                updateSearchButtonState();
            }, 150);
        });

        if (locClear) {
            locClear.addEventListener('click', () => {
                unlockLocInput();
                locInput.focus();
            });
        }
    }

    // Estado inicial
    updateSearchButtonState();
});
</script>



@endsection
