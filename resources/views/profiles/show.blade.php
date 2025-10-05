<x-app-layout>
    {{-- Bootstrap 5 + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Detalle 15% más angosto + scroll (en mobile 100%) --}}
    <style>
        .profile-about{ width:85%; overflow:auto; }
        @media (max-width: 992px){ .profile-about{ width:100%; } }
    </style>

    <x-slot name="header">
        <h1 class="fs-3 fw-semibold m-0">Perfil</h1>
    </x-slot>

    @php
        // Video -> embed (YouTube/Vimeo)
        $videoUrl = trim((string)$profile->video_url);
        $embed = null;
        if ($videoUrl) {
            if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $videoUrl, $m)) {
                $embed = 'https://www.youtube.com/embed/'.$m[1];
            } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $videoUrl, $m)) {
                $embed = 'https://player.vimeo.com/video/'.$m[1];
            }
        }

        // Contacto
        $waDigits = preg_replace('/\D+/', '', (string)$profile->whatsapp);
        $waLink   = $waDigits ? ('https://wa.me/'.$waDigits.'?text='.rawurlencode('Hola, te contacto desde tu perfil.')) : null;
        $email    = $profile->contact_email ?: optional($profile->user)->email;
        $mailto   = $email ? ('mailto:'.$email.'?subject='.rawurlencode('Consulta desde el perfil')) : null;

        $hasPhoto = !empty($profile->photo_path);
        $hasVideo = !empty($embed);
    @endphp

    <div class="py-4 py-md-5">
        <div class="container-xxl">

            {{-- Card principal --}}
            <div class="card border-0 shadow-sm overflow-hidden position-relative">
                <div style="height:4px;background:linear-gradient(90deg,#6366f1,#22d3ee,#14b8a6)"></div>

                {{-- BOTÓN CERRAR / VOLVER --}}
                <button type="button"
                        class="btn btn-light btn-sm rounded-circle position-absolute top-0 end-0 m-2 m-sm-3 shadow"
                        onclick="profileGoBack()" aria-label="Cerrar y volver" title="Volver al listado">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div class="card-body p-3 p-md-4 p-lg-5">
                    <div class="row g-4 align-items-stretch">

                        {{-- IZQ: Media (foto centrada; click abre modal) --}}
                        <div class="col-12 col-lg-5 order-2 order-lg-1 d-flex align-items-center justify-content-center">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#mediaModal" class="text-decoration-none text-center">
                                <div class="p-3 rounded-4 border bg-light d-inline-block" style="max-width:300px">
                                    @if($hasPhoto)
                                        <img src="{{ asset('storage/'.$profile->photo_path) }}"
                                             alt="{{ $profile->display_name }}"
                                             class="img-fluid rounded-3 shadow-sm"
                                             style="object-fit:cover;width:100%;height:auto;cursor:pointer;">
                                    @else
                                        <div class="ratio ratio-4x3 bg-body-secondary rounded-3 d-flex align-items-center justify-content-center" style="cursor:pointer;">
                                            <i class="bi bi-person-circle display-5 text-secondary"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-arrows-fullscreen me-1"></i>Ver grande
                                </div>
                            </a>
                        </div>

                        {{-- DER: Datos --}}
                        <div class="col-12 col-lg-7 order-1 order-lg-2">
                            <h2 class="h3 fw-semibold mb-1">{{ $profile->display_name }}</h2>

                            <div class="text-secondary">
                                {{ $profile->service->name ?? 'Sin especialidad' }}
                                @if($profile->city) · {{ $profile->city }}@endif
                                @if($profile->state){{ $profile->city ? ',' : ' ·' }} {{ $profile->state }}@endif
                            </div>

                            <div class="mt-3 d-flex flex-wrap gap-2">
                                @if($profile->mode_remote)
                                    <span class="badge text-bg-secondary rounded-pill">Remoto</span>
                                @endif
                                @if($profile->mode_presential)
                                    <span class="badge text-bg-secondary rounded-pill">Presencial</span>
                                @endif
                            </div>

                            {{-- Detalle (15% más angosto + scroll) --}}
                            @if($profile->about)
                                <div class="mt-4 d-flex">
                                    <div class="profile-about">{!! $profile->about !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Acciones (debajo de ambas columnas) --}}
                    @if($waLink || $mailto)
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                @if($waLink)
                                    <a href="{{ $waLink }}" target="_blank" rel="noopener"
                                       class="btn btn-success btn-lg rounded-pill px-4">
                                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                                    </a>
                                @endif
                                @if($mailto)
                                    <a href="{{ $mailto }}"
                                       class="btn btn-primary btn-lg rounded-pill px-4">
                                        <i class="bi bi-envelope-fill me-2"></i>Correo
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Modal / Lightbox (foto grande y video si hay) --}}
            @if($hasPhoto || $hasVideo)
                <div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content border-0">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $profile->display_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="mediaCarousel" class="carousel slide" data-bs-interval="false">
                                    <div class="carousel-inner">
                                        @if($hasPhoto)
                                            <div class="carousel-item active">
                                                <img src="{{ asset('storage/'.$profile->photo_path) }}"
                                                     class="d-block w-100" alt="Foto de {{ $profile->display_name }}">
                                            </div>
                                        @endif
                                        @if($hasVideo)
                                            <div class="carousel-item @if(!$hasPhoto) active @endif">
                                                <div class="ratio ratio-16x9">
                                                    <iframe id="videoFrame"
                                                            src="{{ $embed }}"
                                                            title="Video" allowfullscreen loading="lazy"
                                                            class="rounded-bottom"></iframe>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if($hasPhoto && $hasVideo)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#mediaCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Anterior</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#mediaCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Siguiente</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Volver: si hay historial local, back; si no, al Home
        function profileGoBack(){
            try{
                if (document.referrer && new URL(document.referrer).origin === location.origin){
                    history.back();
                } else {
                    window.location.href = "{{ route('home') }}";
                }
            }catch(e){
                window.location.href = "{{ route('home') }}";
            }
        }
    </script>

    @if(!empty($embed))
    <script>
        // Pausar video al cerrar el modal (reset src)
        const mediaModal = document.getElementById('mediaModal');
        mediaModal?.addEventListener('hidden.bs.modal', () => {
            const iframe = document.getElementById('videoFrame');
            if (iframe) {
                const src = iframe.getAttribute('src');
                iframe.setAttribute('src', src);
            }
        });
    </script>
    @endif
</x-app-layout>
