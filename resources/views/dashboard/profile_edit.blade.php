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

                {{-- UBICACIÓN: un único campo con autocompletado + mapeo a country/state/city/address + lat/lng --}}
                <style>
                    .ac-wrap{position:relative}
                    .ac-list{position:absolute;left:0;right:0;z-index:40;background:#fff;border:1px solid #e5e7eb;border-radius:.5rem;box-shadow:0 8px 16px rgba(0,0,0,.08);max-height:16rem;overflow:auto}
                    .ac-item{padding:.5rem .75rem;cursor:pointer}
                    .ac-item:hover{background:#f9fafb}
                </style>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Ubicación</label>
                    <div class="ac-wrap">
                        <div class="flex gap-2">
                            <input id="loc_input" class="border p-2 rounded w-full" autocomplete="off"
                                   placeholder="Ciudad, provincia o dirección…" {{ $locked ? 'disabled' : '' }}>
                            <button type="button" id="btn_myloc"
                                    class="border rounded px-3 text-sm"
                                    {{ $locked ? 'disabled' : '' }}>Mi ubicación</button>
                        </div>
                        <div id="loc_list" class="ac-list hidden"></div>
                    </div>
                    <div class="text-xs text-gray-500" id="coords_hint" style="display:none"></div>

                    {{-- Hidden: coordenadas que se guardarán en profiles --}}
                    <input type="hidden" name="lat" id="lat" value="{{ old('lat', $profile->lat) }}">
                    <input type="hidden" name="lng" id="lng" value="{{ old('lng', $profile->lng) }}">
                </div>

                {{-- Campos que seguimos enviando (rellenos por el autocompletado) --}}
                <div class="hidden md:grid-cols-3 gap-4 mt-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">País (ISO-2)</label>
                        <input name="country" id="country" class="border p-2 rounded w-full"
                               value="{{ old('country',$profile->country) }}" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Provincia/Estado</label>
                        <input name="state" id="state" class="border p-2 rounded w-full"
                               value="{{ old('state',$profile->state) }}" readonly>
                    </div>
                    <div>
                        <label class="hidden text-sm font-medium mb-1">Ciudad</label>
                        <input name="city" id="city" class="border p-2 rounded w-full"
                               value="{{ old('city',$profile->city) }}" readonly>
                    </div>
                </div>

                <div class="hidden">
                    <label class="hidden text-sm font-medium mb-1">Dirección</label>
                    <input name="address" id="address" class="border p-2 rounded w-full"
                           value="{{ old('address',$profile->address) }}" readonly>
                    <p class="text-xs text-gray-500 mt-1">
                        Coordenadas:
                        <span id="latlngText">{{ old('lat', $profile->lat) }}, {{ old('lng', $profile->lng) }}</span>
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

      // Debounce
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
        // Lat/Lng
        setLatLng(item.lat, item.lon);

        // Address parts
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

        // Mostrar legible
        const label = [city, state].filter(Boolean).join(', ') || fullAddr || item.display_name;
        locInput.value = label;

        list.classList.add('hidden'); list.innerHTML='';
      }

      // Listeners
      locInput.addEventListener('input', () => debounce(() => search(locInput.value.trim()), 300));
      document.addEventListener('click', (e) => {
        if(e.target.closest('#loc_list .ac-item')){
          const raw = e.target.closest('.ac-item').dataset.item;
          choose(JSON.parse(raw));
        } else if(!e.target.closest('#loc_list') && e.target !== locInput){
          list.classList.add('hidden');
        }
      });

      // Mi ubicación (reverse geocoding)
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

      // Si ya hay datos previos, mostrarlos
      if(latEl.value && lngEl.value){
        hint.style.display='block';
        hint.textContent=`Coordenadas: ${latEl.value}, ${lngEl.value}`;
      }
    })();
    </script>
</x-app-layout>
