<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Mi Perfil</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 shadow sm:rounded-lg">

            @if (session('status'))
                <div class="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php $locked = isset($pendingEdit) && $pendingEdit; @endphp

            {{-- Aviso de bloqueo si hay pending --}}
            @if($locked)
                <div class="mb-5 rounded border border-amber-200 bg-amber-50 px-3 py-3 text-amber-900 text-sm">
                    <div class="font-medium mb-1">Tenés una edición pendiente de aprobación.</div>
                    <div class="mb-3">
                        Hasta que se apruebe o la anules, no podés modificar el perfil.
                        @if($pendingEdit?->created_at)
                            <span class="text-amber-700">Enviada el {{ $pendingEdit->created_at->format('d/m/Y H:i') }}.</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('dashboard.profile.cancel') }}"
                          onsubmit="return confirm('¿Anular la petición de aprobación?\nSe perderán los cambios enviados.');">
                        @csrf
                        <button class="bg-white border border-red-300 text-red-700 hover:bg-red-50 px-3 py-1.5 rounded">
                            Anular petición
                        </button>
                    </form>
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.profile.save') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Nombre público --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre público</label>
                    <input name="display_name" class="border p-2 rounded w-full"
                           value="{{ old('display_name', $profile->display_name) }}" {{ $locked ? 'disabled' : '' }}>
                </div>

                {{-- Especialidad (usa services como lista desplegable) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Especialidad</label>
                    <select name="service_id" class="border p-2 rounded w-full" {{ $locked ? 'disabled' : '' }}>
                        <option value="">-- Seleccionar --</option>
                        @foreach(($services ?? collect()) as $s)
                            <option value="{{ $s->id }}"
                                {{ (string)old('service_id', $profile->service_id) === (string)$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(isset($services) && $services->isEmpty())
                        <p class="text-xs text-gray-500 mt-1">No hay servicios cargados.</p>
                    @endif
                </div>

                {{-- Modalidad --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Modalidad</label>
                    @php
                        $currentMod = ($profile->mode_remote && $profile->mode_presential) ? 'ambas'
                            : ($profile->mode_remote ? 'remoto' : 'presencial');
                    @endphp
                    <select name="modality" class="border p-2 rounded w-full" {{ $locked ? 'disabled' : '' }}>
                        <option value="remoto" {{ old('modality',$currentMod)==='remoto' ? 'selected' : '' }}>Remoto</option>
                        <option value="ambas" {{ old('modality',$currentMod)==='ambas' ? 'selected' : '' }}>Remoto y presencial</option>
                        <option value="presencial" {{ old('modality',$currentMod)==='presencial' ? 'selected' : '' }}>Presencial</option>
                    </select>
                </div>

                {{-- País/Provincia/Ciudad --}}
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">País (ISO-2)</label>
                        <input name="country" class="border p-2 rounded w-full"
                               value="{{ old('country',$profile->country) }}" {{ $locked ? 'disabled' : '' }}>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Provincia/Estado</label>
                        <input name="state" class="border p-2 rounded w-full" list="stateList"
                               value="{{ old('state',$profile->state) }}" {{ $locked ? 'disabled' : '' }}>
                        <datalist id="stateList"></datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ciudad</label>
                        <input name="city" class="border p-2 rounded w-full" list="cityList"
                               value="{{ old('city',$profile->city) }}" {{ $locked ? 'disabled' : '' }}>
                        <datalist id="cityList"></datalist>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Dirección</label>
                    <input name="address" class="border p-2 rounded w-full"
                           value="{{ old('address',$profile->address) }}" {{ $locked ? 'disabled' : '' }}>
                    {{-- Coordenadas (se completan solas) --}}
                    <input type="hidden" name="lat" id="lat" value="{{ old('lat', $profile->lat) }}">
                    <input type="hidden" name="lng" id="lng" value="{{ old('lng', $profile->lng) }}">
                    <p class="text-xs text-gray-500 mt-1">
                        Coordenadas: <span id="latlngText">
                            {{ old('lat', $profile->lat) }}, {{ old('lng', $profile->lng) }}
                        </span>
                    </p>
                </div>

                {{-- WhatsApp + Email --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">WhatsApp</label>
                        <input name="whatsapp" class="border p-2 rounded w-full"
                               placeholder="+54 9 11 5555-5555"
                               value="{{ old('whatsapp', $profile->whatsapp) }}" {{ $locked ? 'disabled' : '' }}>
                        <p class="text-xs text-gray-500 mt-1">Ingresá tu número con código de país (puede llevar +, espacios o guiones).</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Correo</label>
                        <input name="contact_email" type="email" class="border p-2 rounded w-full"
                               placeholder="tucorreo@dominio.com"
                               value="{{ old('contact_email', $profile->contact_email) }}" {{ $locked ? 'disabled' : '' }}>
                    </div>
                </div>

                {{-- Detalle (rich text) --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Detalle (texto enriquecido)</label>
                    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.4/dist/trix.css">
                    <script src="https://unpkg.com/trix@2.0.4/dist/trix.umd.min.js"></script>
                    <input id="about" type="hidden" name="about" value="{{ old('about', $profile->about) }}" {{ $locked ? 'disabled' : '' }}>
                    <trix-editor input="about" class="trix-content" {{ $locked ? 'contenteditable=false' : '' }}></trix-editor>
                    @if($locked)
                        <p class="text-xs text-gray-500 mt-1">Bloqueado por solicitud pendiente.</p>
                    @endif
                </div>

                {{-- Foto --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Foto</label>
                    <input type="file" name="photo" accept="image/*" {{ $locked ? 'disabled' : '' }}>
                    @if($profile->photo_path)
                        <div class="mt-2">
                            <img src="{{ asset('storage/'.$profile->photo_path) }}" class="h-20 rounded">
                        </div>
                    @endif
                </div>

                {{-- Video --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Video (URL)</label>
                    <input name="video_url" class="border p-2 rounded w-full"
                           placeholder="https://www.youtube.com/watch?v=..."
                           value="{{ old('video_url', $profile->video_url) }}" {{ $locked ? 'disabled' : '' }}>
                </div>

                {{-- Template --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Template</label>
                    <select name="template_key" class="border p-2 rounded w-full" {{ $locked ? 'disabled' : '' }}>
                        <option value="a" {{ old('template_key',$profile->template_key)==='a' ? 'selected' : '' }}>Template A</option>
                        <option value="b" {{ old('template_key',$profile->template_key)==='b' ? 'selected' : '' }}>Template B</option>
                    </select>
                </div>

                <div class="pt-3">
                    <button class="bg-black text-white px-4 py-2 rounded {{ $locked ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $locked ? 'disabled' : '' }}>
                        Enviar a aprobación
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Geocoding + Autocompletado (Nominatim / OSM) --}}
    <script>
    (function(){
        const locked = @json($locked);
        if (locked) return;

        const countryEl = document.querySelector('[name="country"]');
        const stateEl   = document.querySelector('[name="state"]');
        const cityEl    = document.querySelector('[name="city"]');
        const addrEl    = document.querySelector('[name="address"]');
        const latEl     = document.getElementById('lat');
        const lngEl     = document.getElementById('lng');
        const latlngTxt = document.getElementById('latlngText');
        const stateList = document.getElementById('stateList');
        const cityList  = document.getElementById('cityList');

        const debounce = (fn, ms=800) => {
            let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); };
        };

        async function nominatimSearch(q){
            if(!q || q.trim().length < 2) return [];
            const url = new URL('https://nominatim.openstreetmap.org/search');
            url.search = new URLSearchParams({
                format: 'json',
                addressdetails: 1,
                limit: 5,
                q: q,
                // opcional: identificá tu app (mejor práctica)
                email: 'admin@tu-dominio.com'
            });
            try {
                const r = await fetch(url, {headers:{'Accept':'application/json'}});
                if(!r.ok) return [];
                return await r.json();
            } catch { return []; }
        }

        function setLatLng(lat,lng){
            if(lat==null || lng==null) return;
            latEl.value = Number(lat).toFixed(7);
            lngEl.value = Number(lng).toFixed(7);
            if (latlngTxt) latlngTxt.textContent = `${latEl.value}, ${lngEl.value}`;
        }

        // ---- Autocompletar PROVINCIA ----
        const suggestStates = debounce(async ()=>{
            stateList.innerHTML = '';
            const q = `${stateEl.value || ''} ${countryEl.value || ''}`.trim();
            if(!q) return;
            const res = await nominatimSearch(q);
            const seen = new Set();
            res.forEach(item=>{
                const st = item.address?.state;
                if(st && !seen.has(st)){
                    seen.add(st);
                    const opt = document.createElement('option');
                    opt.value = st;
                    stateList.appendChild(opt);
                }
            });
        });

        // ---- Autocompletar CIUDAD ----
        const suggestCities = debounce(async ()=>{
            cityList.innerHTML = '';
            const q = `${cityEl.value || ''} ${stateEl.value || ''} ${countryEl.value || ''}`.trim();
            if(!q) return;
            const res = await nominatimSearch(q);
            const seen = new Set();
            res.forEach(item=>{
                const a = item.address || {};
                const city = a.city || a.town || a.village || a.hamlet;
                if(city && !seen.has(city)){
                    seen.add(city);
                    const opt = document.createElement('option');
                    opt.value = city;
                    cityList.appendChild(opt);
                }
            });
        });

        // ---- Geocodificar dirección completa -> lat/lng ----
        const geocodeFull = debounce(async ()=>{
            const q = [addrEl.value, cityEl.value, stateEl.value, countryEl.value].filter(Boolean).join(', ');
            if(!q) return;
            const res = await nominatimSearch(q);
            if(res.length){
                setLatLng(res[0].lat, res[0].lon);
            }
        });

        // Eventos
        stateEl.addEventListener('input', suggestStates);
        cityEl.addEventListener('input', suggestCities);

        [countryEl, stateEl, cityEl, addrEl].forEach(el=>{
            el.addEventListener('change', geocodeFull);
            el.addEventListener('blur', geocodeFull);
            el.addEventListener('input', debounce(()=>{}, 200)); // solo para activar debounce base
        });

        // Geocodificar al cargar si ya hay datos
        geocodeFull();
    })();
    </script>
</x-app-layout>
