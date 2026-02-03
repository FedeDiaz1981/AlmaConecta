@extends('layouts.app')

@section('title', 'Resultados de búsqueda')

@section('content')
    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

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
                            <div class="text-right min-w-[6rem]">
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
@endsection
