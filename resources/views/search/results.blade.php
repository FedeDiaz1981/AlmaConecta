@extends('layouts.app')

@section('title', 'Resultados de búsqueda - Alma Conecta')

@section('content')
    @php
        $dynamicFilters = $dynamicFilters ?? [
            'enabled' => false,
            'base_total' => 0,
            'has_groups' => false,
            'active_count' => 0,
            'locations' => [],
            'specialties' => [],
            'ratings' => [],
            'modalities' => [],
        ];
        $selectedDynamicFilters = $selectedDynamicFilters ?? [
            'locations' => [],
            'specialties' => [],
            'rating' => null,
            'modality' => null,
        ];
        $showDynamicFilters = $dynamicFilters['enabled'] && $dynamicFilters['has_groups'];
        $clearDynamicParams = request()->except([
            'filter_locations',
            'filter_specialties',
            'filter_rating',
            'filter_modality',
            'page',
        ]);
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="mb-4 text-2xl font-semibold text-silver">
                Resultados de búsqueda
            </h1>

            {{-- Resumen de búsqueda --}}
            <div class="mb-4 rounded-2xl border border-blueMid bg-blueNight/80 px-4 py-3 text-sm text-silver/90">
                <div class="flex flex-wrap items-center gap-2">
                    @if($q)
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Búsqueda:</span>
                            <span class="font-semibold">"{{ $q }}"</span>
                        </span>
                    @endif

                    @if($lat && $lng)
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Ubicación:</span>
                            <span class="font-semibold">{{ $loc ?: 'mi ubicación' }}</span>
                        </span>

                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Radio:</span>
                            <span class="font-semibold">{{ $r }} km</span>
                        </span>
                    @endif

                    @if(!empty($all))
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Modalidad:</span>
                            <span class="font-semibold">Todas</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Remoto:</span>
                            <span class="font-semibold">{{ $remote ? 'Sí' : 'No' }}</span>
                        </span>
                    @endif

                    @if(!empty($word))
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Filtro:</span>
                            <span class="font-semibold">"{{ $word }}"</span>
                        </span>
                    @endif

                    @if(!empty($featured))
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Destacados:</span>
                            <span class="font-semibold">Top 10</span>
                        </span>
                    @endif

                    @if(($sort ?? '') === 'name_desc')
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Orden:</span>
                            <span class="font-semibold">Alfabético (Z–A)</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Orden:</span>
                            <span class="font-semibold">Relevancia</span>
                        </span>
                    @endif

                    @if(($dynamicFilters['active_count'] ?? 0) > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-blueDeep/70 px-3 py-1 text-xs">
                            <span class="opacity-70">Filtros:</span>
                            <span class="font-semibold">{{ $dynamicFilters['active_count'] }}</span>
                        </span>
                    @endif

                    <a href="{{ route('home') }}"
                       class="ml-auto text-xs text-gold hover:text-goldLight">
                        Modificar búsqueda
                    </a>
                </div>
            </div>

            {{-- Filtros --}}
            <form method="GET" action="{{ route('search') }}"
                  class="mb-6 rounded-2xl border border-blueMid/70 bg-blueNight/60 px-4 py-3">
                <input type="hidden" name="q" value="{{ $q }}">
                <input type="hidden" name="search_mode" value="{{ $search_mode ?? 'specialty' }}">
                <input type="hidden" name="province_id" value="{{ $province_id ?? '' }}">
                <input type="hidden" name="province_name" value="{{ $province_name ?? '' }}">
                <input type="hidden" name="city_id" value="{{ $city_id ?? '' }}">
                <input type="hidden" name="city_name" value="{{ $city_name ?? '' }}">
                <input type="hidden" name="remote" value="{{ $remote ? '1' : '0' }}">
                @foreach(($selectedDynamicFilters['locations'] ?? []) as $locationKey)
                    <input type="hidden" name="filter_locations[]" value="{{ $locationKey }}">
                @endforeach
                @foreach(($selectedDynamicFilters['specialties'] ?? []) as $specialtyId)
                    <input type="hidden" name="filter_specialties[]" value="{{ $specialtyId }}">
                @endforeach
                @if(!empty($selectedDynamicFilters['rating']))
                    <input type="hidden" name="filter_rating" value="{{ $selectedDynamicFilters['rating'] }}">
                @endif
                @if(!empty($selectedDynamicFilters['modality']))
                    <input type="hidden" name="filter_modality" value="{{ $selectedDynamicFilters['modality'] }}">
                @endif
                @if(!empty($all))
                    <input type="hidden" name="all" value="1">
                @endif
                @if(!empty($featured))
                    <input type="hidden" name="featured" value="1">
                @endif

                <div class="flex flex-col gap-3 md:flex-row md:items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-silver/70 mb-1">Filtrar por palabra</label>
                        <input type="text"
                               name="word"
                               value="{{ $word ?? '' }}"
                               placeholder="Ej: masajes, reiki, sonido..."
                               class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2 text-sm text-blueDeep placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold">
                    </div>
                    <div class="min-w-[220px]">
                        <label class="block text-xs text-silver/70 mb-1">Ordenar</label>
                        <select name="sort"
                                class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2 text-sm text-blueDeep focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold">
                            <option value="rating_desc" {{ ($sort ?? 'rating_desc') === 'rating_desc' ? 'selected' : '' }}>
                                Relevancia
                            </option>
                            <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>
                                Alfabético (Z–A)
                            </option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                                class="inline-flex items-center rounded-full bg-gold px-5 py-2.5 text-sm font-semibold text-blueDeep shadow-soft hover:bg-goldStrong">
                            Aplicar
                        </button>
                    </div>
                </div>
            </form>

            <div class="{{ $showDynamicFilters ? 'lg:grid lg:grid-cols-[17rem_1fr] lg:gap-5 lg:items-start' : '' }}">
                @if($showDynamicFilters)
                    <aside class="hidden lg:block">
                        <form method="GET" action="{{ route('search') }}"
                              class="sticky top-5 rounded-2xl border border-blueMid/70 bg-blueNight/80 px-4 py-4 text-sm text-silver/85 shadow-soft">
                            <input type="hidden" name="q" value="{{ $q }}">
                            <input type="hidden" name="search_mode" value="{{ $search_mode ?? 'specialty' }}">
                            <input type="hidden" name="word" value="{{ $word ?? '' }}">
                            <input type="hidden" name="sort" value="{{ $sort ?? 'rating_desc' }}">
                            <input type="hidden" name="province_id" value="{{ $province_id ?? '' }}">
                            <input type="hidden" name="province_name" value="{{ $province_name ?? '' }}">
                            <input type="hidden" name="city_id" value="{{ $city_id ?? '' }}">
                            <input type="hidden" name="city_name" value="{{ $city_name ?? '' }}">
                            <input type="hidden" name="remote" value="{{ $remote ? '1' : '0' }}">
                            @if(!empty($all))
                                <input type="hidden" name="all" value="1">
                            @endif
                            @if(!empty($featured))
                                <input type="hidden" name="featured" value="1">
                            @endif

                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-sm font-semibold text-silver">Afinar resultados</h2>
                                    <p class="mt-1 text-xs text-silver/55">
                                        Detectados sobre {{ $dynamicFilters['base_total'] }} resultados.
                                    </p>
                                </div>
                                @if(($dynamicFilters['active_count'] ?? 0) > 0)
                                    <a href="{{ route('search', $clearDynamicParams) }}"
                                       class="text-xs font-semibold text-gold hover:text-goldLight">
                                        Limpiar
                                    </a>
                                @endif
                            </div>

                            <div class="space-y-5">
                                @if(!empty($dynamicFilters['locations']))
                                    <fieldset>
                                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-silver/60">Localidad</legend>
                                        <div class="space-y-2">
                                            @foreach($dynamicFilters['locations'] as $option)
                                                <label class="flex cursor-pointer items-start gap-2 rounded-xl px-2 py-1.5 hover:bg-blueDeep/50">
                                                    <input type="checkbox"
                                                           name="filter_locations[]"
                                                           value="{{ $option['key'] }}"
                                                           class="mt-0.5 h-4 w-4 rounded border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                                           {{ $option['selected'] ? 'checked' : '' }}>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-xs text-silver">{{ $option['label'] }}</span>
                                                        <span class="text-[11px] text-silver/50">{{ $option['count'] }} resultados</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endif

                                @if(!empty($dynamicFilters['specialties']))
                                    <fieldset>
                                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-silver/60">Especialidad</legend>
                                        <div class="space-y-2">
                                            @foreach($dynamicFilters['specialties'] as $option)
                                                <label class="flex cursor-pointer items-start gap-2 rounded-xl px-2 py-1.5 hover:bg-blueDeep/50">
                                                    <input type="checkbox"
                                                           name="filter_specialties[]"
                                                           value="{{ $option['id'] }}"
                                                           class="mt-0.5 h-4 w-4 rounded border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                                           {{ $option['selected'] ? 'checked' : '' }}>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-xs text-silver">{{ $option['label'] }}</span>
                                                        <span class="text-[11px] text-silver/50">{{ $option['count'] }} resultados</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endif

                                @if(!empty($dynamicFilters['ratings']))
                                    <fieldset>
                                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-silver/60">Calificación</legend>
                                        <div class="space-y-2">
                                            @foreach($dynamicFilters['ratings'] as $option)
                                                <label class="flex cursor-pointer items-start gap-2 rounded-xl px-2 py-1.5 hover:bg-blueDeep/50">
                                                    <input type="radio"
                                                           name="filter_rating"
                                                           value="{{ $option['key'] }}"
                                                           class="mt-0.5 h-4 w-4 border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                                           {{ $option['selected'] ? 'checked' : '' }}>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-xs text-silver">{{ $option['label'] }}</span>
                                                        <span class="text-[11px] text-silver/50">{{ $option['count'] }} resultados</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endif

                                @if(!empty($dynamicFilters['modalities']))
                                    <fieldset>
                                        <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-silver/60">Modalidad</legend>
                                        <div class="space-y-2">
                                            @foreach($dynamicFilters['modalities'] as $option)
                                                <label class="flex cursor-pointer items-start gap-2 rounded-xl px-2 py-1.5 hover:bg-blueDeep/50">
                                                    <input type="radio"
                                                           name="filter_modality"
                                                           value="{{ $option['key'] }}"
                                                           class="mt-0.5 h-4 w-4 border-blueMid bg-blueDeep text-gold focus:ring-gold"
                                                           {{ $option['selected'] ? 'checked' : '' }}>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-xs text-silver">{{ $option['label'] }}</span>
                                                        <span class="text-[11px] text-silver/50">{{ $option['count'] }} resultados</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endif

                                <button type="submit"
                                        class="w-full rounded-full bg-gold px-4 py-2.5 text-sm font-semibold text-blueDeep shadow-soft hover:bg-goldStrong">
                                    Aplicar filtros
                                </button>
                            </div>
                        </form>
                    </aside>
                @endif

                <div>
            {{-- Resultados --}}
            <div class="max-h-[70vh] overflow-y-auto pr-1">
                @forelse($results as $p)
                    <article class="mb-4 rounded-2xl border border-blueMid/70 bg-blueNight/80 px-4 py-4 shadow-soft">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">

                            {{-- Info principal --}}
                            <div class="space-y-1">
                                <a href="{{ route('profiles.show', $p->slug) }}"
                                   class="text-lg font-semibold text-silver hover:text-gold transition">
                                    {{ $p->display_name }}
                                </a>

                                <div class="text-xs text-silver/70 flex flex-wrap gap-2">
                                    <span>
                                        @if($p->specialties && $p->specialties->count())
                                            {{ $p->specialties->pluck('name')->take(2)->join(' · ') }}
                                            @if($p->specialties->count() > 2)
                                                <span class="opacity-70">+{{ $p->specialties->count() - 2 }}</span>
                                            @endif
                                        @else
                                            Sin especialidad
                                        @endif
                                    </span>

                                    @if($p->mode_remote)
                                        <span class="inline-flex items-center rounded-full bg-emerald-500/15 px-2 py-0.5 text-[11px] font-semibold text-emerald-300 border border-emerald-500/40">
                                            Remoto
                                        </span>
                                    @endif

                                    @if($p->mode_presential)
                                        <span class="inline-flex items-center rounded-full bg-sky-500/10 px-2 py-0.5 text-[11px] font-semibold text-sky-200 border border-sky-500/40">
                                            Presencial
                                        </span>
                                    @endif

                                    @if($p->city || $p->state)
                                        <span class="inline-flex items-center gap-1 text-[11px] text-silver/70">
                                            •
                                            <span>
                                                @if($p->city)
                                                    {{ $p->city }}
                                                @endif
                                                @if($p->state)
                                                    {{ $p->city ? ', ' : '' }}{{ $p->state }}
                                                @endif
                                            </span>
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($p->about))
                                    <p class="mt-2 text-xs text-silver/75 line-clamp-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($p->about), 200) }}
                                    </p>
                                @endif
                            </div>

                            {{-- Distancia --}}
                            <div class="text-right min-w-[6rem] space-y-2">
                                @if(($p->reviews_count ?? 0) > 0)
                                    <div class="inline-flex flex-col items-end rounded-xl bg-blueDeep/70 px-3 py-1 text-xs text-silver/90 border border-blueMid/70">
                                        <span class="font-semibold text-gold">{{ number_format((float) $p->reviews_avg_rating, 1) }} / 5</span>
                                        <span class="text-[11px] text-silver/55">{{ $p->reviews_count }} reseñas</span>
                                    </div>
                                @endif

                                @if(!is_null($p->distance ?? null))
                                    <div class="inline-flex items-center rounded-full bg-blueDeep/80 px-3 py-1 text-xs text-silver/90 border border-blueMid/70">
                                        {{ number_format($p->distance, 1) }} km
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-blueMid bg-blueNight/80 px-6 py-6 text-sm text-silver/85 shadow-soft">
                        <p class="font-semibold mb-1">No encontramos resultados.</p>
                        <p class="text-silver/70">
                            Probá ampliando el radio de búsqueda, cambiando la ubicación
                            o activando la opción de modalidad remota.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            @if($results->hasPages())
                <div class="mt-6">
                    {{ $results->withQueryString()->links() }}
                </div>
            @endif
                </div>
            </div>
        </div>
    </div>
@endsection

