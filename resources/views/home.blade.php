{{-- resources/views/home.blade.php --}}
<x-app-layout>
    {{-- Bootstrap 5 + Icons solo en esta vista --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <x-slot name="header">
        <h1 class="fs-3 fw-semibold m-0">Encontrá profesionales</h1>
    </x-slot>

    <div class="py-5">
        <div class="container-xxl">
            {{-- Hero card con CTA para abrir el modal --}}
            <div class="card border-0 shadow-sm overflow-hidden">
                <div style="height:4px;background:linear-gradient(90deg,#6366f1,#22d3ee,#14b8a6)"></div>
                <div class="card-body p-4 p-md-5 text-center">
                    <h2 class="fw-semibold mb-2">Buscá por especialidad y zona</h2>
                    <p class="text-secondary mb-4">Ej.: “acupuntura”, “yoga”, “coach”, “fonoaudiología”…</p>
                    <button class="btn btn-primary btn-lg rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#searchModal">
                        <i class="bi bi-search me-2"></i> Abrir búsqueda
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: búsqueda en columna (responsive) --}}
    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="searchForm" class="modal-content" method="GET" action="{{ route('search') }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-search me-2"></i>Nueva búsqueda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="vstack gap-3">
                        {{-- Qué buscás --}}
                        <div>
                            <label class="form-label">Especialidad o palabra clave</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="q" id="q" class="form-control"
                                       placeholder="¿Qué buscás? (ej: acupuntura, yoga)"
                                       value="{{ request('q') }}">
                            </div>
                        </div>

                        {{-- Ubicación con autocomplete --}}
                        <div class="position-relative">
                            <label class="form-label">Ubicación</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="loc" id="loc" class="form-control"
                                       placeholder="Ciudad, barrio o indicación (vacío = usar mi ubicación)"
                                       autocomplete="off" value="{{ request('loc') }}">
                                <button class="btn btn-outline-secondary" type="button" id="btnMyLocation">
                                    <i class="bi bi-crosshair me-1"></i> Mi ubicación
                                </button>
                            </div>
                            {{-- Dropdown sugerencias --}}
                            <div id="locDropdown" class="dropdown-menu w-100 mt-1"></div>
                        </div>

                        {{-- Radio + remoto --}}
                        <div class="row g-3 align-items-center">
                            <div class="col-6 col-sm-4 col-md-3">
                                <label class="form-label mb-1">Radio (km)</label>
                                <input type="number" min="1" step="1" name="r" class="form-control"
                                       value="{{ request('r', 25) }}">
                            </div>
                            <div class="col-6 col-sm-8 col-md-9">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" role="switch" id="remote" name="remote"
                                           value="1" {{ request('remote', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remote">Incluir modalidad remota</label>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden coords (las completa el script) --}}
                        <input type="hidden" id="lat" name="lat" value="{{ request('lat') }}">
                        <input type="hidden" id="lng" name="lng" value="{{ request('lng') }}">

                        <div id="geoStatus" class="form-text text-muted"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button id="searchBtn" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Autocomplete + geolocalización (Nominatim + Geolocation API) --}}
    <script>
    (function () {
        const form   = document.getElementById('searchForm');
        const btn    = document.getElementById('searchBtn');
        const status = document.getElementById('geoStatus');
        const loc    = document.getElementById('loc');
        const dd     = document.getElementById('locDropdown');
        const latEl  = document.getElementById('lat');
        const lngEl  = document.getElementById('lng');
        const myBtn  = document.getElementById('btnMyLocation');

        const debounce = (fn, ms=350) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };
        const setBusy  = (v) => { btn.disabled = v; btn.innerHTML = v ? '<span class="spinner-border spinner-border-sm me-2"></span>Buscando…' : '<i class="bi bi-search me-2"></i>Buscar'; };

        function showSuggestions(items){
            if (!items.length) { dd.classList.remove('show'); dd.innerHTML=''; return; }
            dd.innerHTML = items.map(i => `
              <button type="button" class="dropdown-item" data-lat="${i.lat}" data-lng="${i.lon}">
                <i class="bi bi-geo-alt me-2"></i>${i.display_name}
              </button>
            `).join('');
            dd.classList.add('show');
        }

        const suggest = debounce(async () => {
            const q = loc.value.trim();
            latEl.value = ''; lngEl.value = '';
            if (q.length < 3) { showSuggestions([]); return; }
            try {
                const url = new URL('https://nominatim.openstreetmap.org/search');
                url.searchParams.set('q', q);
                url.searchParams.set('format','json');
                url.searchParams.set('limit','6');
                url.searchParams.set('accept-language','es');
                const res = await fetch(url, { headers: { 'Accept':'application/json' } });
                const data = await res.json();
                showSuggestions(Array.isArray(data) ? data : []);
            } catch { showSuggestions([]); }
        }, 350);

        loc.addEventListener('input', suggest);
        dd.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-lat]');
            if (!btn) return;
            latEl.value = btn.dataset.lat;
            lngEl.value = btn.dataset.lng;
            loc.value   = btn.textContent.trim();
            dd.classList.remove('show');
        });
        document.addEventListener('click', (e) => { if (!dd.contains(e.target) && e.target !== loc) dd.classList.remove('show'); });

        myBtn.addEventListener('click', async () => {
            status.textContent = 'Obteniendo tu ubicación…';
            const coords = await getCurrentPosition();
            if (coords) {
                latEl.value = coords.lat;
                lngEl.value = coords.lng;
                loc.value   = '';
                status.textContent = 'Ubicación cargada.';
            } else {
                status.textContent = 'No pudimos obtener tu ubicación.';
            }
        });

        function getCurrentPosition() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) return resolve(null);
                navigator.geolocation.getCurrentPosition(
                    p => resolve({ lat: p.coords.latitude, lng: p.coords.longitude }),
                    _ => resolve(null),
                    { enableHighAccuracy: true, timeout: 7000 }
                );
            });
        }

        async function geocodeText(q) {
            const url = new URL('https://nominatim.openstreetmap.org/search');
            url.searchParams.set('q', q);
            url.searchParams.set('format','json');
            url.searchParams.set('limit','1');
            url.searchParams.set('accept-language','es');
            try {
                const res = await fetch(url, { headers: { 'Accept':'application/json' } });
                const data = await res.json();
                if (Array.isArray(data) && data.length) {
                    return { lat: data[0].lat, lng: data[0].lon };
                }
            } catch {}
            return null;
        }

        form.addEventListener('submit', async (ev) => {
            // Si ya tenemos coords, seguimos
            if (latEl.value && lngEl.value) return;

            ev.preventDefault();
            setBusy(true);
            status.textContent = '';

            const locText = loc.value.trim();
            let coords = null;

            if (locText) {
                status.textContent = 'Buscando ubicación…';
                coords = await geocodeText(locText);
                if (!coords) status.textContent = 'No se encontró esa ubicación. Se buscará sin radio.';
            } else {
                status.textContent = 'Obteniendo tu ubicación…';
                coords = await getCurrentPosition();
                if (!coords) status.textContent = 'No pudimos obtener tu ubicación. Se buscará sin radio.';
            }

            if (coords) { latEl.value = coords.lat; lngEl.value = coords.lng; }
            setBusy(false);
            HTMLFormElement.prototype.submit.call(form);
        });
    })();
    </script>
</x-app-layout>
