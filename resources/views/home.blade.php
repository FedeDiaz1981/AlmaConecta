@extends('layouts.app')

@section('title', 'Alma Conecta - Bienestar holístico')

@section('content')

{{-- ============================= --}}
{{-- HERO / PORTADA (con imagen IA de fondo) --}}
{{-- ============================= --}}
<section
    class="relative w-full min-h-[80vh] md:min-h-[95vh] text-silver bg-cover bg-top overflow-hidden"
    style="background-image: url('{{ asset('hero_lma_conecta.png') }}');"
>
    {{-- Capa oscura para que se lea el texto --}}
    <div class="absolute inset-0 bg-black/40"></div>

    {{-- Luz dorada encima (tu gradiente) --}}
    <div class="absolute inset-0 opacity-50"
         style="background: radial-gradient(circle at 25% 15%, rgba(203,160,67,0.45), transparent 55%);">
    </div>

    {{-- CONTENIDO --}}
    <div class="relative max-w-8xl mx-auto md:ml-[5%] px-6 w-full pt-20 md:pt-32 pb-12">
        <div class="w-full md:max-w-4xl mx-auto md:mx-0 text-center md:text-left">

            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4">
                Encontrá tu espacio de <span class="text-gold">bienestar holístico</span>
            </h1>

            <p class="text-silver/80 text-base md:text-lg mb-10">
                Conectá con terapeutas, facilitadores y espacios de bienestar en un solo lugar.
            </p>

            {{-- Buscador principal --}}
            <form method="GET" action="{{ route('search') }}"
                  class="bg-blueInk/80 border border-blueNight rounded-2xl p-5 shadow-soft backdrop-blur-md
                         mx-auto md:mx-0">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- q: especialidad (solo opciones existentes) --}}
                    <div class="flex flex-col md:col-span-2 relative">
                        <label class="text-[16px] font-semibold tracking-wide uppercase text-silver/60 mb-1 text-left">
                            ¿Qué estás buscando?
                        </label>

                        <div class="relative">
                            <input type="text"
                                   name="q"
                                   id="q"
                                   autocomplete="off"
                                   placeholder="Reiki, Yoga, Constelaciones..."
                                   value="{{ request('q') }}"
                                   class="w-full bg-blueNight/70 border border-blueNight text-silver text-sm rounded-xl
                                          px-3 pr-9 py-2 focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold">

                            {{-- Botón limpiar selección --}}
                            <button type="button"
                                    id="q-clear"
                                    class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-silver/60 hover:text-silver text-xs">
                                ✕
                            </button>
                        </div>

                        {{-- id de la especialidad seleccionada --}}
                        <input type="hidden"
                               name="specialty_id"
                               id="specialty_id"
                               value="{{ request('specialty_id') }}">

                        {{-- Contenedor de sugerencias --}}
                        <div id="q-suggestions"
                             class="absolute left-0 right-0 top-full mt-1 bg-blueNight border border-blueMid rounded-xl shadow-soft
                                    max-h-56 overflow-auto text-sm hidden z-20">
                            {{-- se completa por JS --}}
                        </div>
                    </div>

                    {{-- loc: ubicación --}}
                    <div class="flex flex-col">
                        <label class="text-[16px] font-semibold tracking-wide uppercase text-silver/60 mb-1 text-left">
                            ¿Dónde?
                        </label>
                        <input type="text"
                               name="loc"
                               id="loc"
                               placeholder="Ciudad o barrio"
                               value="{{ request('loc') }}"
                               class="w-full bg-blueNight/70 border border-blueNight text-silver text-sm rounded-xl
                                      px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold">
                        <input type="hidden" id="lat" name="lat" value="{{ request('lat') }}">
                        <input type="hidden" id="lng" name="lng" value="{{ request('lng') }}">
                    </div>

                    {{-- radio + modalidad + botón --}}
                    <div class="flex flex-col justify-end gap-2">
                        <div class="flex flex-col">
                            <label class="text-[16px] font-semibold tracking-wide uppercase text-silver/60 mb-1 text-left">
                                Área de búsqueda
                            </label>
                            <select name="r"
                                    class="w-full bg-blueNight/70 border border-blueNight text-silver text-xs rounded-xl
                                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold">
                                @foreach([5, 10, 20, 50, 100] as $radius)
                                    <option value="{{ $radius }}" {{ (int)request('r', 20) === $radius ? 'selected' : '' }}>
                                        Hasta {{ $radius }} km
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label class="flex items-center gap-2 text-[16px] text-silver/70 text-left">
                            <input type="checkbox"
                                   name="remote"
                                   value="1"
                                   class="rounded border-blueNight bg-blueNight/70 text-gold focus:ring-gold"
                                   {{ request()->boolean('remote', true) ? 'checked' : '' }}>
                            <span>Incluir modalidad online/remota</span>
                        </label>

                        <button type="submit"
                                class="w-full px-6 py-2.5 rounded-xl bg-gold text-blueDeep text-sm font-semibold
                                       shadow-soft hover:bg-goldStrong transition">
                            Buscar
                        </button>
                    </div>
                </div>
            </form>

            <p class="mt-4 text-[11px] text-silver/60">
                Tip: podés buscar directamente por especialidad.
            </p>
        </div>
    </div>
</section>



{{-- ============================= --}}
{{-- BLOQUE APP / BENEFICIOS (equivalente a “publicidad”) --}}
{{-- ============================= --}}
<section class="bg-blueNight py-12 md:py-16">
    <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16 items-center">

        {{-- Imagen / mockup app (podés reemplazar por tu imagen real) --}}
        <div class="flex justify-center md:justify-start">
            <div class="relative h-72 w-40 rounded-3xl bg-gradient-to-br from-blueMid to-blueDeep shadow-strong flex items-center justify-center">
                {{-- Acá podrías poner un <img> con tu mockup --}}
                <span class="text-[11px] text-silver/70 px-4 text-center">
                    Aquí va el mockup de la app / sitio de Alma Conecta
                </span>
            </div>
        </div>

        {{-- Texto --}}
        <div>
            <h2 class="text-2xl font-semibold text-silver mb-3">
                Bienestar a un clic de distancia
            </h2>
            <p class="text-sm text-silver/80 mb-4">
                Explorá prácticas, filtrá por ubicación y modalidad, y conectá con el espacio que mejor resuene con vos.
            </p>

            <ul class="space-y-2 text-sm text-silver/85 mb-6">
                <li>• Búsqueda por especialidad y ubicación.</li>
                <li>• Perfiles verificados y moderados.</li>
                <li>• Modalidad presencial y remota.</li>
            </ul>

            {{-- CTA doble --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('search') }}"
                   class="px-5 py-2.5 rounded-full bg-gold text-blueDeep text-sm font-semibold shadow-soft hover:bg-goldStrong transition">
                    Explorar espacios
                </a>
                <a href="{{ route('dashboard.profile.edit') }}"
                   class="px-5 py-2.5 rounded-full border border-gold text-gold text-sm font-semibold hover:bg-gold/10 transition">
                    Soy facilitador/a
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================= --}}
{{-- PRÁCTICAS MÁS BUSCADAS        --}}
{{-- ============================= --}}
<section class="bg-blueDeep py-12 md:py-16">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-silver">
                Prácticas más buscadas
            </h2>
            <a href="{{ route('search') }}" class="text-sm text-gold hover:text-goldLight">
                Ver todas
            </a>
        </div>

        @if(isset($topSpecialties) && count($topSpecialties))
            <div class="flex flex-wrap gap-3">
                @foreach($topSpecialties as $specialty)
                    <a href="{{ route('search', ['q' => $specialty->name]) }}"
                       class="px-4 py-2 rounded-full bg-blueNight border border-blueMid text-silver text-xs
                              hover:border-gold hover:text-gold transition">
                        {{ $specialty->name }}
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-silver/60 text-sm">
                Todavía no hay prácticas destacadas cargadas.
            </p>
        @endif
    </div>
</section>

{{-- ============================= --}}
{{-- FACILITADORES DESTACADOS      --}}
{{-- ============================= --}}
<section class="bg-blueNight py-12 md:py-16">
    <div class="max-w-6xl mx-auto px-6">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-silver">
                Facilitadores destacados
            </h2>
            <a href="{{ route('search') }}" class="text-sm text-gold hover:text-goldLight">
                Ver más
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(($featuredProfiles ?? []) as $profile)
                <article class="bg-blueDeep p-5 rounded-2xl shadow-soft border border-blueInk/60 transition
                               hover:shadow-strong hover:border-gold/40">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-12 w-12 rounded-full bg-blueNight overflow-hidden">
                            {{-- foto de perfil si la tenés --}}
                        </div>
                        <div>
                            <h3 class="text-silver font-semibold text-sm">
                                {{ $profile->display_name }}
                            </h3>
                            <p class="text-silver/70 text-xs">
                                {{ $profile->specialties->pluck('name')->take(2)->join(' · ') }}
                            </p>
                        </div>
                    </div>

                    <p class="text-silver/75 text-xs mb-4 line-clamp-3">
                        {{ Str::limit($profile->about, 140) }}
                    </p>

                    <div class="flex justify-between items-center text-[11px] text-silver/60">
                        <span>{{ $profile->city }}, {{ $profile->state }}</span>
                        <a href="{{ route('profiles.show', $profile->slug) }}"
                           class="font-semibold text-gold hover:text-goldLight">
                            Ver perfil
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input      = document.getElementById('q');
        const hidId      = document.getElementById('specialty_id');
        const box        = document.getElementById('q-suggestions');
        const clearBtn   = document.getElementById('q-clear');
        const form       = input ? input.form : null;

        if (!input || !hidId || !box || !form) return;

        let timeoutId = null;
        let lastItems = [];

        const hideBox = () => {
            box.classList.add('hidden');
            box.innerHTML = '';
        };

        const lockInput = () => {
            input.readOnly = true;
            input.classList.add('cursor-default');
            if (clearBtn) clearBtn.classList.remove('hidden');
        };

        const unlockInput = () => {
            input.readOnly = false;
            input.value = '';
            hidId.value = '';
            input.classList.remove('cursor-default');
            if (clearBtn) clearBtn.classList.add('hidden');
        };

        const showSuggestions = (items) => {
            lastItems = items;
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
                    hidId.value = item.id;          // guardamos id válido
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
                showSuggestions(data);
            } catch (e) {
                console.error(e);
            }
        };

        // Si ya viene una especialidad seleccionada (por querystring), bloquear
        if (hidId.value && input.value.trim() !== '') {
            lockInput();
        }

        // Cada vez que escribe, invalidamos el id hasta que elija una opción
        input.addEventListener('input', () => {
            if (input.readOnly) return;
            const term = input.value.trim();
            hidId.value = '';          // texto cambió → no hay selección válida
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fetchSuggestions(term), 250);
        });

        // Al salir del campo: si no hay selección válida, limpiar texto
        input.addEventListener('blur', () => {
            setTimeout(() => {
                hideBox();
                if (!hidId.value) {
                    input.value = '';
                }
            }, 150);
        });

        // Mostrar sugerencias al enfocar si ya hay texto y no está bloqueado
        input.addEventListener('focus', () => {
            if (input.readOnly) return;
            const term = input.value.trim();
            if (term.length >= 2 && !hidId.value) {
                fetchSuggestions(term);
            }
        });

        // Botón limpiar: desbloquea y permite elegir otra opción
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                unlockInput();
                input.focus();
            });
        }

        // Validar al enviar: si hay texto pero no specialty_id, no dejamos
        form.addEventListener('submit', (e) => {
            const term = input.value.trim();

            // Permitir búsqueda sin especialidad (por ubicación, etc)
            if (term === '') {
                return;
            }

            if (!hidId.value) {
                e.preventDefault();
                alert('Seleccioná una especialidad de la lista. No se pueden agregar opciones nuevas.');
            }
        });
    });
</script>

@endsection
