@extends('layouts.app')

@section('title', 'Panel de administración')

@section('content')
    @php
        // Bloqueo si hay edición pendiente
        $locked = isset($pendingEdit) && $pendingEdit;
    @endphp

    <div class="py-10 bg-blueDeep">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Título de página --}}
            <div class="mb-6">
                <h1 class="font-semibold text-2xl text-silver">
                    Mi perfil profesional
                </h1>
                <p class="mt-1 text-sm text-silver/70">
                    Actualizá la información que van a ver tus potenciales clientes.
                </p>
            </div>

            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6 sm:p-8 space-y-6">

                {{-- mensajes de estado --}}
                @if (session('status'))
                    <div class="mb-2 rounded-xl border border-emerald-500/60 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200">
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

                {{-- Aviso de bloqueo si hay pending --}}
                @if($locked)
                    <div class="rounded-2xl border border-amber-400/70 bg-amber-900/30 px-4 py-4 text-amber-100 text-sm flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-800 text-amber-100 text-xs font-semibold">
                                !
                            </span>
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

                <form method="POST"
                      action="{{ route('dashboard.profile.save') }}"
                      enctype="multipart/form-data"
                      class="space-y-8">
                    @csrf

                    {{-- BLOQUE: Datos principales --}}
                    <section class="space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">
                                    Datos principales
                                </h3>
                                <p class="text-xs text-silver/60 mt-1">
                                    Nombre público y especialidades que verá el usuario.
                                </p>
                            </div>
                            @if($profile->status === 'approved')
                                <span class="inline-flex items-center rounded-full bg-emerald-900/60 px-3 py-1 text-xs font-medium text-emerald-200 border border-emerald-500/60">
                                    Perfil publicado
                                </span>
                            @endif
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- Nombre público --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-silver mb-1">Nombre público</label>
                                <input name="display_name"
                                       class="w-full rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2.5 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30 disabled:text-silver/40"
                                       value="{{ old('display_name', $profile->display_name) }}"
                                       {{ $locked ? 'disabled' : '' }}>
                            </div>

                            {{-- Servicio oculto (compatibilidad) --}}
                            <input type="hidden"
                                   name="service_id"
                                   value="{{ old('service_id', $profile->service_id) }}">

                            {{-- ESPECIALIDADES: buscador + chips --}}
                            @php
                                $allSpecialties = ($specialties ?? collect())->map(fn($s) => [
                                    'id'   => $s->id,
                                    'name' => $s->name,
                                ]);

                                $profileSpecialties = collect($profile->specialties ?? []);

                                $selectedIds = old(
                                    'specialties',
                                    $profileSpecialties->pluck('id')->toArray()
                                );
                                $selectedIds = array_map('intval', $selectedIds);
                            @endphp

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-silver mb-1">Especialidades</label>

                                @if($allSpecialties->isEmpty())
                                    <p class="text-xs text-silver/60">
                                        Todavía no hay especialidades configuradas. Consultá con el administrador.
                                    </p>
                                @else
                                    <div id="specialty-widget"
                                         data-specialties='@json($allSpecialties)'
                                         data-locked="{{ $locked ? '1' : '0' }}"
                                         class="space-y-2">
                                        <input type="text"
                                               id="specialty-search"
                                               class="w-full rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30"
                                               placeholder="Escribí para buscar especialidades..."
                                               {{ $locked ? 'disabled' : '' }}>

                                        <div id="specialty-results"
                                             class="border border-blueMid rounded-xl bg-blueDeep/95 shadow-soft mt-1 hidden max-h-52 overflow-auto text-sm divide-y divide-blueNight/60">
                                            {{-- items generados por JS --}}
                                        </div>

                                        <div id="specialty-selected"
                                             class="flex flex-wrap gap-2 mt-1">
                                            @foreach($allSpecialties as $s)
                                                @if(in_array($s['id'], $selectedIds))
                                                    <span class="inline-flex items-center px-2.5 py-1 bg-gold/10 text-gold text-xs rounded-full specialty-chip border border-gold/40"
                                                          data-id="{{ $s['id'] }}">
                                                        {{ $s['name'] }}
                                                        @unless($locked)
                                                            <button type="button"
                                                                    class="ml-1 text-gold hover:text-goldLight text-xs specialty-chip-remove"
                                                                    aria-label="Quitar">
                                                                ×
                                                            </button>
                                                        @endunless
                                                    </span>
                                                    <input type="hidden" name="specialties[]" value="{{ $s['id'] }}">
                                                @endif
                                            @endforeach
                                        </div>
                                        <p class="text-[11px] text-silver/60">
                                            Podés seleccionar varias especialidades.
                                        </p>
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
                        <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">
                            Modalidad y ubicación
                        </h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- Modalidad --}}
                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Modalidad</label>
                                @php
                                    $currentMod = ($profile->mode_remote && $profile->mode_presential) ? 'ambas'
                                        : ($profile->mode_remote ? 'remoto' : 'presencial');
                                @endphp
                                <select name="modality"
                                        class="w-full rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2.5 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30"
                                        {{ $locked ? 'disabled' : '' }}>
                                    <option value="remoto" {{ old('modality',$currentMod)==='remoto' ? 'selected' : '' }}>Remoto</option>
                                    <option value="ambas" {{ old('modality',$currentMod)==='ambas' ? 'selected' : '' }}>Remoto y presencial</option>
                                    <option value="presencial" {{ old('modality',$currentMod)==='presencial' ? 'selected' : '' }}>Presencial</option>
                                </select>
                            </div>

                            {{-- UBICACIÓN --}}
                            <style>
                                .ac-wrap{position:relative}
                                .ac-list{
                                    position:absolute;
                                    left:0;
                                    right:0;
                                    z-index:40;
                                    background:#020617;
                                    border:1px solid #1e293b;
                                    border-radius:.75rem;
                                    box-shadow:0 10px 20px rgba(15,23,42,.55);
                                    max-height:16rem;
                                    overflow:auto
                                }
                                .ac-item{
                                    padding:.5rem .75rem;
                                    cursor:pointer;
                                    font-size:.875rem;
                                    color:#e5e7eb;
                                }
                                .ac-item:hover{background:#0f172a}
                            </style>

                            <div class="md:col-span-2 space-y-2">
                                <label class="block text-sm font-medium text-silver">Ubicación</label>
                                <div class="ac-wrap">
                                    <div class="flex gap-2">
                                        <input id="loc_input"
                                               class="flex-1 rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2.5 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30"
                                               autocomplete="off"
                                               placeholder="Ciudad, provincia o dirección…"
                                               {{ $locked ? 'disabled' : '' }}>
                                        <button type="button" id="btn_myloc"
                                                class="inline-flex items-center rounded-xl border border-blueMid bg-blueNight/40 px-3 py-2 text-xs font-medium text-silver hover:bg-blueNight/70 disabled:opacity-50"
                                                {{ $locked ? 'disabled' : '' }}>
                                            Mi ubicación
                                        </button>
                                    </div>
                                    <div id="loc_list" class="ac-list hidden"></div>
                                </div>
                                <div class="text-xs text-silver/60" id="coords_hint" style="display:none"></div>

                                <input type="hidden" name="lat" id="lat" value="{{ old('lat', $profile->lat) }}">
                                <input type="hidden" name="lng" id="lng" value="{{ old('lng', $profile->lng) }}">
                            </div>

                            <div class="hidden md:grid-cols-3 gap-4 mt-3 md:col-span-2">
                                <div>
                                    <label class="block text-xs font-medium text-silver/70 mb-1">País (ISO-2)</label>
                                    <input name="country" id="country"
                                           class="w-full rounded-lg border border-blueMid bg-blueNight/40 px-3 py-2 text-xs text-silver"
                                           value="{{ old('country',$profile->country) }}" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-silver/70 mb-1">Provincia/Estado</label>
                                    <input name="state" id="state"
                                           class="w-full rounded-lg border border-blueMid bg-blueNight/40 px-3 py-2 text-xs text-silver"
                                           value="{{ old('state',$profile->state) }}" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-silver/70 mb-1">Ciudad</label>
                                    <input name="city" id="city"
                                           class="w-full rounded-lg border border-blueMid bg-blueNight/40 px-3 py-2 text-xs text-silver"
                                           value="{{ old('city',$profile->city) }}" readonly>
                                </div>
                            </div>

                            <div class="hidden md:col-span-2">
                                <input name="address" id="address"
                                       class="w-full rounded-lg border border-blueMid bg-blueNight/40 px-3 py-2 text-xs text-silver"
                                       value="{{ old('address',$profile->address) }}" readonly>
                                <p class="text-[11px] text-silver/60 mt-1">
                                    Coordenadas:
                                    <span id="latlngText">{{ old('lat', $profile->lat) }}, {{ old('lng', $profile->lng) }}</span>
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- BLOQUE: Contacto y detalle --}}
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">
                            Contacto y descripción
                        </h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- WhatsApp --}}
                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">WhatsApp</label>
                                <input name="whatsapp"
                                       class="w-full rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2.5 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30"
                                       placeholder="+54 9 11 5555-5555"
                                       value="{{ old('whatsapp', $profile->whatsapp) }}"
                                       {{ $locked ? 'disabled' : '' }}>
                                <p class="text-[11px] text-silver/60 mt-1">
                                    Ingresá tu número con código de país (puede llevar +, espacios o guiones).
                                </p>
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Correo</label>
                                <input name="contact_email" type="email"
                                       class="w-full rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2.5 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30"
                                       placeholder="tucorreo@dominio.com"
                                       value="{{ old('contact_email', $profile->contact_email) }}"
                                       {{ $locked ? 'disabled' : '' }}>
                            </div>
                        </div>

                        {{-- Detalle --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-silver mb-1">Detalle (texto enriquecido)</label>
                            <link rel="stylesheet" href="https://unpkg.com/trix@2.0.4/dist/trix.css">
                            <script src="https://unpkg.com/trix@2.0.4/dist/trix.umd.min.js"></script>
                            <input id="about" type="hidden" name="about"
                                   value="{{ old('about', $profile->about) }}"
                                   {{ $locked ? 'disabled' : '' }}>
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
                        <h3 class="text-sm font-semibold text-silver uppercase tracking-wide">
                            Imagen y formato
                        </h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- Foto --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-silver mb-1">Foto</label>
                                <input type="file" name="photo" accept="image/*"
                                       class="block w-full text-sm text-silver file:mr-3 file:rounded-lg file:border-0 file:bg-gold/10 file:px-3 file:py-2 file:text-xs file:font-medium file:text-gold hover:file:bg-gold/20 disabled:opacity-60"
                                       {{ $locked ? 'disabled' : '' }}>
                                @if($profile->photo_path)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/'.$profile->photo_path) }}"
                                             class="h-20 w-20 rounded-xl object-cover border border-blueMid">
                                    </div>
                                @endif
                            </div>

                            {{-- Video --}}
                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Video (URL)</label>
                                <input name="video_url"
                                       class="w-full rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2.5 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30"
                                       placeholder="https://www.youtube.com/watch?v=..."
                                       value="{{ old('video_url', $profile->video_url) }}"
                                       {{ $locked ? 'disabled' : '' }}>
                            </div>

                            {{-- Template --}}
                            <div>
                                <label class="block text-sm font-medium text-silver mb-1">Template</label>
                                <select name="template_key"
                                        class="w-full rounded-xl border border-blueMid bg-blueNight/60 px-3 py-2.5 text-sm text-silver shadow-sm focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold disabled:bg-blueNight/30"
                                        {{ $locked ? 'disabled' : '' }}>
                                    <option value="a" {{ old('template_key',$profile->template_key)==='a' ? 'selected' : '' }}>Template A</option>
                                    <option value="b" {{ old('template_key',$profile->template_key)==='b' ? 'selected' : '' }}>Template B</option>
                                </select>
                            </div>
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

    {{-- Autocompletado (Nominatim / OpenStreetMap) --}}
    <script>
    (function(){
      const locked = @json($locked);
      if (locked) return;

      const $ = s => document.querySelector(s);
      const locInput = $('#loc_input');
      const list = $('#loc_list');
      const myBtn = $('#btn_myloc');
      const latEl = $('#lat'), lngEl = $('#lng');
      const countryEl = $('#country'), stateEl = $('#state'), cityEl = $('#city'), addrEl = $('#address');
      const hint = $('#coords_hint');
      const latlngTxt = $('#latlngText');

      let t=null; const debounce=(fn,ms=250)=>{ clearTimeout(t); t=setTimeout(fn,ms); };

      function setLatLng(lat,lng){
        if(typeof lat==='undefined' || typeof lng==='undefined') return;
        latEl.value = Number(lat).toFixed(7);
        lngEl.value = Number(lng).toFixed(7);
        latlngTxt.textContent = `${latEl.value}, ${lngEl.value}`;
        hint.style.display = 'block';
        hint.textContent = `Coordenadas: ${latEl.value}, ${lngEl.value}`;
      }

      function render(items){
        if(!items || !items.length){ list.classList.add('hidden'); list.innerHTML=''; return; }
        list.innerHTML = items.map(it =>
          `<div class="ac-item" data-item='${JSON.stringify(it).replaceAll("'", "&#39;")}'>
             ${it.display_name}
           </div>`).join('');
        list.classList.remove('hidden');
      }

      async function search(q){
        if(!q || q.length<3){ render([]); return; }
        const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=6&accept-language=es&q=${encodeURIComponent(q)}`;
        try{
          const r = await fetch(url, {headers:{'Accept':'application/json'}});
          const data = await r.json();
          render(data);
        }catch{ render([]); }
      }

      function choose(item){
        setLatLng(item.lat, item.lon);

        const a = item.address || {};
        const city = a.city || a.town || a.village || a.hamlet || '';
        const state = a.state || '';
        const country = (a.country_code || '').toUpperCase();
        const road = a.road || a.pedestrian || a.path || '';
        const hn = a.house_number ? ` ${a.house_number}` : '';
        const fullAddr = (item.type === 'house' || road) ? `${road}${hn}` : (item.display_name || '');

        countryEl.value = country;
        stateEl.value   = state;
        cityEl.value    = city;
        addrEl.value    = fullAddr;

        const label = [city, state].filter(Boolean).join(', ') || fullAddr || item.display_name;
        locInput.value = label;

        list.classList.add('hidden'); list.innerHTML='';
      }

      locInput.addEventListener('input', () => debounce(() => search(locInput.value.trim()), 300));
      document.addEventListener('click', (e) => {
        if(e.target.closest('#loc_list .ac-item')){
          const raw = e.target.closest('.ac-item').dataset.item;
          choose(JSON.parse(raw));
        } else if(!e.target.closest('#loc_list') && e.target !== locInput){
          list.classList.add('hidden');
        }
      });

      myBtn.addEventListener('click', () => {
        if(!navigator.geolocation){ alert('Tu navegador no permite geolocalización.'); return; }
        navigator.geolocation.getCurrentPosition(async pos=>{
          const {latitude, longitude} = pos.coords;
          setLatLng(latitude, longitude);
          try{
            const url=`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&addressdetails=1&accept-language=es`;
            const r=await fetch(url); const j=await r.json();
            choose({lat:latitude, lon:longitude, address:j.address||{}, display_name:j.display_name||'', type:j.type||''});
          }catch(e){}
        }, ()=>alert('No pudimos obtener tu ubicación.'));
      });

      if(latEl.value && lngEl.value){
        hint.style.display='block';
        hint.textContent=`Coordenadas: ${latEl.value}, ${lngEl.value}`;
      }
    })();
    </script>

    {{-- Widget de especialidades: buscador + chips --}}
    <script>
    (function() {
        const widget = document.getElementById('specialty-widget');
        if (!widget) return;

        const locked = widget.dataset.locked === '1';
        if (locked) return;

        const specialties   = JSON.parse(widget.dataset.specialties || '[]');
        const input         = document.getElementById('specialty-search');
        const results       = document.getElementById('specialty-results');
        const selectedWrap  = document.getElementById('specialty-selected');

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
            hidden.type  = 'hidden';
            hidden.name  = 'specialties[]';
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

            const qLower   = q.toLowerCase();
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

            const id   = parseInt(btn.dataset.id, 10);
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
::contentReference[oaicite:0]{index=0}
