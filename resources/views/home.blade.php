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

                    {{-- q: especialidad --}}
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

                        <input type="hidden" name="specialty_id" id="specialty_id" value="{{ request('specialty_id') }}">

                        <div id="q-suggestions"
                            class="absolute left-0 right-0 top-full mt-1 bg-blueNight border border-blueMid rounded-xl shadow-soft
                                    max-h-56 overflow-auto text-sm hidden z-20">
                        </div>
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
                                disabled
                                class="hero-button disabled:opacity-50 disabled:cursor-not-allowed mx-auto w-1/2">
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
                                h-[320px] md:h-[480px]   {{-- MÁS ALTAS para que no se corten al hacer hover --}}
                                rounded-3xl overflow-hidden
                                border border-blueMid/70 bg-blueNight/80
                                transition-transform duration-300 ease-out
                                hover:-translate-y-1 hover:scale-[1.03] hover:shadow-strong hover:border-gold/80">

                            {{-- Fondo con imagen si existe --}}
                            @if($specialty->featured_image_path)
                                <img
                                    src="{{ asset('storage/'.$specialty->featured_image_path) }}"
                                    alt="{{ $specialty->name }}"
                                    class="absolute inset-0 w-full h-full object-cover"
                                >
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-blueNight via-blueDeep to-blueInk"></div>
                            @endif

                            {{-- Capa oscura para que no se pierda el texto --}}
                            <div class="absolute inset-0
                                        bg-gradient-to-b from-black/30 via-black/35 to-black/60
                                        group-hover:from-black/25 group-hover:via-black/30 group-hover:to-black/55
                                        transition-colors duration-300">
                            </div>

                            {{-- CONTENIDO --}}
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

                    {{-- Badge top-right --}}
                    <div class="absolute right-4 top-4 text-[10px] uppercase tracking-[0.14em]
                                px-2 py-1 rounded-full bg-gold/10 text-gold border border-gold/40">
                        Destacado
                    </div>

                    <div class="flex items-center gap-3 mb-4">
                        {{-- Avatar --}}
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

                    {{-- Detalle / about (solo texto, sin HTML visible) --}}
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


{{-- ============================= --}}
{{-- JS: Autocomplete + carrusel --}}
{{-- ============================= --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchBtn = document.getElementById('search-btn');

        const updateSearchButtonState = () => {
            if (!searchBtn) return;

            const qEl   = document.getElementById('q');
            const qIdEl = document.getElementById('specialty_id');
            const locEl = document.getElementById('loc');
            const latEl = document.getElementById('lat');
            const lngEl = document.getElementById('lng');

            const qValid   = qEl && qIdEl && qEl.value.trim() !== '' && qIdEl.value;
            const locValid = locEl && latEl && lngEl &&
                             locEl.value.trim() !== '' && latEl.value && lngEl.value;

            searchBtn.disabled = !(qValid && locValid);
        };

        // ----- AUTOCOMPLETE ESPECIALIDAD -----
        const input      = document.getElementById('q');
        const hidId      = document.getElementById('specialty_id');
        const box        = document.getElementById('q-suggestions');
        const clearBtn   = document.getElementById('q-clear');
        const form       = input ? input.form : null;

        if (input && hidId && box && form) {
            let timeoutId = null;

            const hideBox = () => {
                box.classList.add('hidden');
                box.innerHTML = '';
            };

            const lockInput = () => {
                input.readOnly = true;
                input.classList.add('cursor-default');
                if (clearBtn) clearBtn.classList.remove('hidden');
                updateSearchButtonState();
            };

            const unlockInput = () => {
                input.readOnly = false;
                input.value = '';
                hidId.value = '';
                input.classList.remove('cursor-default');
                if (clearBtn) clearBtn.classList.add('hidden');
                updateSearchButtonState();
            };

            const showSuggestions = (items) => {
                if (!items.length) {
                    hideBox();
                    return;
                }

                box.innerHTML = '';
                items.forEach(item => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.textContent = item.name;
                    option.className =
                        'w-full text-left px-3 py-2 hover:bg-blueMid/60 text-silver text-sm';
                    option.addEventListener('click', () => {
                        input.value = item.name;
                        hidId.value = item.id;
                        hideBox();
                        lockInput();
                    });
                    box.appendChild(option);
                });

                box.classList.remove('hidden');
            };

            const fetchSuggestions = async (term) => {
                if (term.length < 2 || input.readOnly) {
                    hideBox();
                    return;
                }
                try {
                    const url = "{{ route('specialties.suggest') }}?q=" + encodeURIComponent(term);
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    showSuggestions(Array.isArray(data) ? data : []);
                } catch (e) {
                    console.error(e);
                }
            };

            if (hidId.value && input.value.trim() !== '') {
                lockInput();
            }

            input.addEventListener('input', () => {
                if (input.readOnly) return;
                const term = input.value.trim();
                hidId.value = '';
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => fetchSuggestions(term), 250);
                updateSearchButtonState();
            });

            input.addEventListener('blur', () => {
                setTimeout(() => {
                    hideBox();
                    if (!hidId.value) {
                        input.value = '';
                    }
                    updateSearchButtonState();
                }, 150);
            });

            input.addEventListener('focus', () => {
                if (input.readOnly) return;
                const term = input.value.trim();
                if (term.length >= 2 && !hidId.value) {
                    fetchSuggestions(term);
                }
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    unlockInput();
                    input.focus();
                });
            }

            form.addEventListener('submit', (e) => {
                updateSearchButtonState();

                const term = input.value.trim();
                if (!term || !hidId.value) {
                    e.preventDefault();
                    alert('Seleccioná una especialidad de la lista.');
                    return;
                }

                const locEl = document.getElementById('loc');
                const latEl = document.getElementById('lat');
                const lngEl = document.getElementById('lng');

                if (!locEl || !latEl || !lngEl ||
                    !locEl.value.trim() || !latEl.value || !lngEl.value) {
                    e.preventDefault();
                    alert('Elegí una ubicación de las sugerencias.');
                    return;
                }
            });
        }

        // ----- AUTOCOMPLETE UBICACIÓN -----
        const locInput = document.getElementById('loc');
        const latEl    = document.getElementById('lat');
        const lngEl    = document.getElementById('lng');
        const locBox   = document.getElementById('loc-suggestions');
        const locClear = document.getElementById('loc-clear');

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
                    btn.className =
                        'w-full text-left px-3 py-2 hover:bg-blueMid/60 text-silver text-sm';
                    btn.dataset.lat = i.lat;
                    btn.dataset.lng = i.lon;
                    btn.textContent = i.display_name;

                    btn.addEventListener('click', () => {
                        locInput.value = i.display_name;
                        latEl.value    = i.lat;
                        lngEl.value    = i.lon;
                        hideLocBox();
                        lockLocInput();
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

                    const res = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) {
                        hideLocBox();
                        updateSearchButtonState();
                        return;
                    }
                    const data = await res.json();
                    showLocSuggestions(Array.isArray(data) ? data : []);
                } catch (e) {
                    console.error(e);
                    hideLocBox();
                } finally {
                    updateSearchButtonState();
                }
            };

            if (locInput.value.trim() !== '' && latEl.value && lngEl.value) {
                lockLocInput();
            }

            locInput.addEventListener('input', () => {
                if (locInput.readOnly) return;
                const q = locInput.value.trim();
                latEl.value = '';
                lngEl.value = '';
                clearTimeout(locTimeout);
                locTimeout = setTimeout(() => fetchLocSuggestions(q), 350);
                updateSearchButtonState();
            });

            locInput.addEventListener('focus', () => {
                if (locInput.readOnly) return;
                const q = locInput.value.trim();
                if (q.length >= 3 && !latEl.value && !lngEl.value) {
                    fetchLocSuggestions(q);
                }
            });

            locInput.addEventListener('blur', () => {
                setTimeout(() => {
                    hideLocBox();
                    if (!latEl.value || !lngEl.value) {
                        locInput.value = '';
                    }
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

        // CARRUSEL PRÁCTICAS DESTACADAS
        const spCarousel = document.getElementById('specialty-carousel');
        const spPrev     = document.getElementById('specialty-prev');
        const spNext     = document.getElementById('specialty-next');

        if (spCarousel && spPrev && spNext) {
            const step = 260; // px a desplazar por click

            spPrev.addEventListener('click', () => {
                spCarousel.scrollBy({ left: -step, behavior: 'smooth' });
            });

            spNext.addEventListener('click', () => {
                spCarousel.scrollBy({ left: step, behavior: 'smooth' });
            });
        }


        updateSearchButtonState();
    });
</script>

@endsection
