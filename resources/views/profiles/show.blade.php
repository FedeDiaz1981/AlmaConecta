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

                {{-- Botones de contacto --}}
                @if($waLink || $mailto)
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
</script>

@endsection
