@extends('layouts.app')

@section('title', $profile->display_name . ' - Alma Conecta')

@section('content')

@php
    $videoUrl = trim((string)$profile->video_url);
    $embed = null;
    if ($videoUrl) {
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $videoUrl, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $videoUrl, $m)) {
            $embed = 'https://player.vimeo.com/video/'.$m[1];
        }
    }

    $waDigits = preg_replace('/\D+/', '', (string)$profile->whatsapp);
    $waLink   = $waDigits ? ('https://wa.me/'.$waDigits.'?text='.rawurlencode('Hola, te contacto desde tu perfil en Alma Conecta.')) : null;
    $email    = $profile->contact_email ?: optional($profile->user)->email;
    $mailto   = $email ? ('mailto:'.$email.'?subject='.rawurlencode('Consulta desde tu perfil en Alma Conecta')) : null;

    $hasPhoto = !empty($profile->photo_path);
    $hasVideo = !empty($embed);
    $isClient = auth()->check() && (auth()->user()->role ?? null) === 'client';
@endphp

<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Card principal --}}
        <div class="relative rounded-3xl border border-blueMid bg-blueNight/90 shadow-soft overflow-hidden">

            {{-- Borde superior degradado --}}
            <div class="h-1 w-full"
                 style="background:linear-gradient(90deg,#6366f1,#22d3ee,#14b8a6)"></div>

            {{-- Botón cerrar / volver --}}
            <button type="button"
                    onclick="profileGoBack()"
                    class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-blueDeep/90 text-silver/80 hover:bg-blueDeep hover:text-silver shadow-sm border border-blueMid text-xs"
                    title="Volver">
                ✕
            </button>

            <div class="p-5 md:p-7">
                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-500/60 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-6 items-start">

                    {{-- Foto / media --}}
                    <div class="flex justify-center lg:justify-start">
                        <button type="button"
                                @if($hasPhoto || $hasVideo)
                                    onclick="openMediaModal()"
                                @endif
                                class="group inline-block rounded-3xl border border-blueMid bg-blueDeep/60 p-3 shadow-soft hover:border-gold/70 hover:bg-blueDeep/80 transition text-left max-w-xs w-full">
                            @if($hasPhoto)
                                <img src="{{ asset('storage/'.$profile->photo_path) }}"
                                     alt="{{ $profile->display_name }}"
                                     class="w-full h-auto rounded-2xl object-cover shadow-md">
                            @else
                                <div class="aspect-[4/3] w-full rounded-2xl bg-blueDeep/80 border border-blueMid flex items-center justify-center">
                                    <span class="text-4xl">👤</span>
                                </div>
                            @endif

                            @if($hasPhoto || $hasVideo)
                                <div class="mt-2 text-[11px] text-silver/60 flex items-center gap-1">
                                    <span class="text-xs">🔍</span>
                                    <span>Ver en grande</span>
                                    @if($hasVideo)
                                        <span class="text-gold/80">· ver video</span>
                                    @endif
                                </div>
                            @endif
                        </button>
                    </div>

                    {{-- Info texto --}}
                    <div class="space-y-4">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-semibold text-silver">
                                {{ $profile->display_name }}
                            </h1>

                            <div class="mt-1 text-sm text-silver/75 flex flex-wrap gap-x-2 gap-y-1">
                                <span>
                                    @if($profile->specialties && $profile->specialties->count())
                                        {{ $profile->specialties->pluck('name')->take(2)->join(' · ') }}
                                        @if($profile->specialties->count() > 2)
                                            <span class="opacity-70">+{{ $profile->specialties->count() - 2 }}</span>
                                        @endif
                                    @else
                                        Sin especialidad
                                    @endif
                                </span>

                                @if($profile->city || $profile->state)
                                    <span class="opacity-60">·</span>
                                    <span>
                                        @if($profile->city)
                                            {{ $profile->city }}
                                        @endif
                                        @if($profile->state)
                                            {{ $profile->city ? ', ' : '' }}{{ $profile->state }}
                                        @endif
                                    </span>
                                @endif
                            </div>

                            @if(($reviewsCount ?? 0) > 0)
                                <div class="mt-2 flex items-center gap-2 text-sm text-silver/80">
                                    <span class="text-gold font-semibold">{{ $avgRating }}/5</span>
                                    <span class="text-silver/60">({{ $reviewsCount }})</span>
                                    <span class="text-gold">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= round($avgRating) ? '' : 'opacity-30' }}">★</span>
                                        @endfor
                                    </span>
                                </div>
                            @endif

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if($profile->mode_remote)
                                    <span class="inline-flex items-center rounded-full bg-emerald-500/15 px-3 py-1 text-[11px] font-semibold text-emerald-200 border border-emerald-500/40">
                                        Remoto
                                    </span>
                                @endif
                                @if($profile->mode_presential)
                                    <span class="inline-flex items-center rounded-full bg-sky-500/15 px-3 py-1 text-[11px] font-semibold text-sky-200 border border-sky-500/40">
                                        Presencial
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($profile->about)
                            <div class="mt-2 text-sm leading-relaxed text-silver/85 max-h-[420px] overflow-auto pr-1 space-y-3">
                                {!! $profile->about !!}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Botones de contacto (solo clientes logueados) --}}
                @if(auth()->check() && (auth()->user()->role ?? null) === 'client' && ($waLink || $mailto))
                    <div class="mt-6 pt-4 border-t border-blueMid/60">
                        <div class="flex flex-wrap justify-center gap-3">
                            @if($waLink)
                                <a href="{{ $waLink }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="inline-flex items-center gap-2 rounded-full bg-emerald-500 text-blueDeep px-6 py-2.5 text-sm font-semibold shadow-soft hover:bg-emerald-400 transition">
                                    <span>WhatsApp</span>
                                </a>
                            @endif

                            @if($mailto)
                                <a href="{{ $mailto }}"
                                   class="inline-flex items-center gap-2 rounded-full bg-gold text-blueDeep px-6 py-2.5 text-sm font-semibold shadow-soft hover:bg-goldStrong transition">
                                    <span>Escribirme por mail</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @elseif($waLink || $mailto)
                    <div class="mt-6 pt-4 border-t border-blueMid/60">
                        <p class="text-center text-sm text-silver/80">
                            Para contactar a un profesional es obligatorio tener cuenta.
                            <a href="{{ route('login') }}" class="text-gold hover:text-goldLight underline">Iniciar sesión</a>
                            o
                            <a href="{{ route('register', ['account_type' => 'client']) }}" class="text-gold hover:text-goldLight underline">crear una cuenta gratuita</a>.
                        </p>
                    </div>
                @endif

                {{-- Reseñas --}}
                <div class="mt-8 pt-4 border-t border-blueMid/60">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-silver uppercase tracking-wide">Reseñas</h2>
                        @if($reviewsCount ?? 0)
                            <div class="text-sm text-silver/80">
                                <span class="text-gold font-semibold">{{ $avgRating }}/5</span>
                                <span class="text-silver/60">({{ $reviewsCount }})</span>
                            </div>
                        @else
                            <div class="text-xs text-silver/60">Sin reseñas aún</div>
                        @endif
                    </div>

                    @if(($reviewsCount ?? 0) > 0)
                        <div class="mt-4 space-y-4">
                            @foreach($profile->reviews as $review)
                                <div class="rounded-xl border border-blueMid/60 bg-blueDeep/60 p-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-sm text-silver font-medium">
                                            {{ $review->user->name ?? 'Usuario' }}
                                        </div>
                                        <div class="text-xs text-silver/60">
                                            {{ optional($review->created_at)->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    <div class="mt-2 text-sm text-gold">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= (int)$review->rating ? '' : 'opacity-30' }}">★</span>
                                        @endfor
                                    </div>
                                    <p class="mt-2 text-sm text-silver/85">
                                        {{ $review->comment }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($isClient)
                        <form id="review-form"
                              method="POST"
                              action="{{ route('reviews.store', $profile) }}"
                              class="mt-6 space-y-4">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Tu puntaje</label>
                                <div class="flex flex-wrap gap-2 text-sm text-silver/80">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio"
                                                   name="rating"
                                                   value="{{ $i }}"
                                                   class="text-gold focus:ring-gold border-blueMid bg-blueDeep/60"
                                                   {{ (int)old('rating', $userReview?->rating) === $i ? 'checked' : '' }}>
                                            <span>{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                                @error('rating')
                                    <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Comentario</label>
                                <textarea name="comment"
                                          rows="4"
                                          class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold"
                                          placeholder="Contanos tu experiencia">{{ old('comment', $userReview?->comment) }}</textarea>
                                @error('comment')
                                    <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                        </form>
                        <div class="flex items-center justify-end gap-3">
                            @if($userReview)
                                <form method="POST"
                                      action="{{ route('reviews.destroy', $profile) }}"
                                      onsubmit="return confirm('¿Eliminar tu reseña?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center rounded-full border border-red-400/60 px-4 py-2 text-sm font-semibold text-red-200 hover:bg-red-900/30">
                                        Borrar reseña
                                    </button>
                                </form>
                            @endif
                            <button type="submit"
                                    form="review-form"
                                    class="inline-flex items-center rounded-full bg-gold px-5 py-2.5 text-sm font-semibold text-blueDeep shadow-soft hover:bg-goldStrong">
                                {{ $userReview ? 'Actualizar reseña' : 'Enviar reseña' }}
                            </button>
                        </div>
                    @elseif(auth()->check())
                        <p class="mt-4 text-sm text-silver/70">
                            Solo los usuarios que buscan profesionales pueden dejar reseñas.
                        </p>
                    @else
                        <p class="mt-4 text-sm text-silver/70">
                            Para puntuar y comentar necesitás una cuenta.
                            <a href="{{ route('login') }}" class="text-gold hover:text-goldLight underline">Iniciar sesión</a>
                            o
                            <a href="{{ route('register', ['account_type' => 'client']) }}" class="text-gold hover:text-goldLight underline">crear una cuenta gratuita</a>.
                        </p>
                    @endif
                </div>

                {{-- Reportar perfil (solo clientes logueados) --}}
                @if($isClient)
                    <div class="mt-6 pt-4 border-t border-blueMid/60 text-center">
                        <button type="button"
                                onclick="openReportModal()"
                                class="text-xs text-red-200/90 underline hover:text-red-200">
                            Reportar perfil
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal media (foto + video) --}}
        @if($hasPhoto || $hasVideo)
            <div id="media-modal"
                 class="fixed inset-0 z-40 hidden items-center justify-center bg-black/80 px-4">
                <div class="relative w-full max-w-5xl max-h-[90vh] overflow-auto rounded-3xl bg-blueNight border border-blueMid shadow-strong">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-blueMid/70">
                        <h2 class="text-sm font-semibold text-silver">
                            {{ $profile->display_name }}
                        </h2>
                        <button type="button"
                                onclick="closeMediaModal()"
                                class="h-8 w-8 flex items-center justify-center rounded-full bg-blueDeep text-silver/80 hover:text-silver hover:bg-blueDeep/80 text-xs">
                            ✕
                        </button>
                    </div>

                    <div class="p-4 space-y-4">
                        @if($hasPhoto)
                            <div class="w-full">
                                <img src="{{ asset('storage/'.$profile->photo_path) }}"
                                     alt="Foto de {{ $profile->display_name }}"
                                     class="w-full h-auto rounded-2xl object-contain">
                            </div>
                        @endif

                        @if($hasVideo)
                            <div class="w-full">
                                <div class="aspect-video w-full rounded-2xl overflow-hidden border border-blueMid/70 bg-black">
                                    <iframe id="videoFrame"
                                            src="{{ $embed }}"
                                            title="Video de {{ $profile->display_name }}"
                                            allowfullscreen
                                            loading="lazy"
                                            class="w-full h-full border-0"></iframe>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal reporte --}}
        @if($isClient)
            <div id="report-modal"
                 class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 px-4">
                <div class="relative w-full max-w-lg rounded-3xl bg-blueNight border border-blueMid shadow-strong">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-blueMid/70">
                        <h2 class="text-sm font-semibold text-silver">
                            Reportar perfil
                        </h2>
                        <button type="button"
                                onclick="closeReportModal()"
                                class="h-8 w-8 flex items-center justify-center rounded-full bg-blueDeep text-silver/80 hover:text-silver hover:bg-blueDeep/80 text-xs">
                            ✕
                        </button>
                    </div>

                    <form method="POST" action="{{ route('reports.store', $profile) }}" class="p-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-silver mb-1">Motivo de la observación</label>
                            <textarea name="reason"
                                      rows="4"
                                      class="w-full rounded-xl border border-blueMid bg-white/95 px-3 py-2.5 text-sm text-blueDeep placeholder-slate-500 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400"
                                      placeholder="Contanos qué observaste">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="text-red-300 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="button"
                                    onclick="closeReportModal()"
                                    class="inline-flex items-center rounded-full border border-blueMid/70 px-4 py-2 text-sm font-semibold text-silver/80 hover:bg-blueDeep/60">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-red-500/90 px-5 py-2.5 text-sm font-semibold text-white shadow-soft hover:bg-red-500">
                                Enviar reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    function profileGoBack() {
        try {
            if (document.referrer && new URL(document.referrer).origin === window.location.origin) {
                history.back();
            } else {
                window.location.href = "{{ route('home') }}";
            }
        } catch (e) {
            window.location.href = "{{ route('home') }}";
        }
    }

    function openMediaModal() {
        const modal = document.getElementById('media-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMediaModal() {
        const modal = document.getElementById('media-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // resetear iframe para parar el video
        const iframe = document.getElementById('videoFrame');
        if (iframe) {
            const src = iframe.getAttribute('src');
            iframe.setAttribute('src', src);
        }
    }

    // Cerrar modal con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMediaModal();
        }
    });

    // Cerrar modal si se hace click en el fondo oscuro
    document.addEventListener('click', (e) => {
        const modal = document.getElementById('media-modal');
        if (!modal || modal.classList.contains('hidden')) return;
        if (e.target === modal) {
            closeMediaModal();
        }
    });

    function openReportModal() {
        const modal = document.getElementById('report-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeReportModal() {
        const modal = document.getElementById('report-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('click', (e) => {
        const modal = document.getElementById('report-modal');
        if (!modal || modal.classList.contains('hidden')) return;
        if (e.target === modal) {
            closeReportModal();
        }
    });
</script>

@if($errors->has('reason'))
<script>
    openReportModal();
</script>
@endif

@endsection
