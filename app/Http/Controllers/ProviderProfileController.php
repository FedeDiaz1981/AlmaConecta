<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\{Profile, Service, Edit, Specialty};

class ProviderProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        $profile = Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
                'slug'         => Str::slug($user->name) . '-' . $user->id,
            ]
        );

        // edición pendiente (para bloquear el form)
        $pendingEdit = Edit::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Servicios
        $services = Service::orderBy('name')->get();

        // Especialidades (disciplinas) activas
        $specialties = Specialty::where('active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.profile_edit', compact(
            'profile',
            'services',
            'pendingEdit',
            'specialties'
        ));
    }

    /**
     * Cliente HTTP:
     * - En local: sin verificar SSL (para evitar cURL error 60 en Windows)
     * - En otros entornos: verificación normal
     */
    protected function httpClient()
    {
        return app()->environment('local')
            ? Http::withoutVerifying()
            : Http::withOptions([]);
    }

    /**
     * Normaliza texto para comparar (minúsculas, sin tildes, espacios).
     */
    protected function normalizePlace(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = preg_replace('/\s+/', ' ', $s);

        // Normalización simple de acentos
        $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n'];
        $s = strtr($s, $map);

        // Limpieza leve de signos comunes
        $s = str_replace(['.', ',', ';'], '', $s);

        return trim($s);
    }

    /**
     * Alias/casos especiales para comparación de provincias/ciudades.
     * (ej: CABA)
     */
    protected function normalizeWithAliases(string $s): string
    {
        $n = $this->normalizePlace($s);

        // Alias CABA
        if ($n === 'caba' || $n === 'capital federal' || $n === 'ciudad autonoma de buenos aires') {
            return 'ciudad autonoma de buenos aires';
        }

        return $n;
    }

    /**
     * Geocodifica una dirección con Nominatim y devuelve [lat, lng].
     * Cachea para no pegarle siempre al servicio.
     */
    protected function geocodeAddress(string $query): ?array
    {
        $q = trim($query);
        if ($q === '') return null;

        $cacheKey = 'geo:addr:' . md5($q);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($q) {
            $client = $this->httpClient();

            $resp = $client->withHeaders([
                    'User-Agent' => 'alma-conecta/1.0 (+https://example.com)',
                ])
                ->timeout(12)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $q,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 0,
                    'polygon_geojson' => 0,
                    'accept-language' => 'es',
                    'countrycodes' => 'ar',
                ]);

            if (!$resp->ok()) {
                return null;
            }

            $data = $resp->json();
            if (!is_array($data) || empty($data[0])) {
                return null;
            }

            $r = $data[0];

            if (!isset($r['lat'], $r['lon'])) {
                return null;
            }

            return [
                'lat' => (float) $r['lat'],
                'lng' => (float) $r['lon'],
            ];
        });
    }

    /**
     * Reverse geocoding (Nominatim) para obtener addressdetails.
     */
    protected function reverseDetails(float $lat, float $lng): ?array
    {
        $client = $this->httpClient();

        $resp = $client->withHeaders([
                'User-Agent' => 'alma-conecta/1.0 (+https://example.com)',
            ])
            ->timeout(12)
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'addressdetails' => 1,
                'accept-language' => 'es',
                'zoom' => 18,
            ]);

        if (!$resp->ok()) return null;

        $j = $resp->json();
        if (!is_array($j)) return null;

        return is_array($j['address'] ?? null) ? $j['address'] : null;
    }

    /**
     * Valida que las coords caigan dentro de la ciudad/provincia seleccionadas.
     */
    protected function coordsBelongToSelectedCity(array $selected, float $lat, float $lng): bool
    {
        $addr = $this->reverseDetails($lat, $lng);
        if (!$addr) return false;

        $revState = (string)($addr['state'] ?? '');
        $revCity  = (string)(
            $addr['city'] ??
            ($addr['town'] ??
            ($addr['village'] ??
            ($addr['hamlet'] ??
            ($addr['municipality'] ?? ''))))
        );

        $chosenState = (string)($selected['province_name'] ?? '');
        $chosenCity  = (string)($selected['city_name'] ?? '');

        if (trim($chosenState) === '' || trim($chosenCity) === '') return false;

        $aState = $this->normalizeWithAliases($revState);
        $aCity  = $this->normalizeWithAliases($revCity);
        $bState = $this->normalizeWithAliases($chosenState);
        $bCity  = $this->normalizeWithAliases($chosenCity);

        if ($aState === $bState && $aCity === $bCity) return true;

        if ($aState === $bState) {
            if ($aCity !== '' && $bCity !== '') {
                if (str_contains($aCity, $bCity) || str_contains($bCity, $aCity)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Dirección: debe tener altura (al menos 1 dígito)
     */
    protected function addressHasNumber(?string $address): bool
    {
        $a = trim((string)$address);
        if ($a === '') return false;
        return preg_match('/\d+/', $a) === 1;
    }

    public function saveDraft(Request $request)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->firstOrFail();

        // si ya hay un pending, no permitimos enviar otro
        $alreadyPending = Edit::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return back()
                ->withErrors(['general' => 'Ya tenés una petición en revisión. Anulála para poder volver a editar.'])
                ->withInput();
        }

        $data = $request->validate([
            'display_name'   => 'required|string|max:255',
            'service_id'     => 'nullable|exists:services,id',
            'modality'       => 'required|in:remoto,ambas,presencial',
            'about'          => 'nullable|string|max:10000',
            'video_url'      => 'nullable|url|max:1024',
            'photo'          => 'nullable|image|max:2048',

            // GeoRef (ids + nombres)
            'province_id'    => 'nullable|string|max:50',
            'province_name'  => 'nullable|string|max:120',
            'city_id'        => 'nullable|string|max:80',
            'city_name'      => 'nullable|string|max:120',

            // Dirección textual (sin ciudad/provincia)
            'address'        => 'nullable|string|max:255',
            'address_extra'  => 'nullable|string|max:120',

            // coords (desde autocomplete)
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',

            // contacto
            'whatsapp'       => 'nullable|string|max:30',
            'contact_email'  => 'nullable|email|max:255',
            'template_key'   => 'required|in:a,b',

            // especialidades
            'specialties'    => 'required|array|min:1',
            'specialties.*'  => 'integer|exists:specialties,id',
        ], [
            'display_name.required' => 'El nombre público es obligatorio.',
            'modality.required'     => 'La modalidad es obligatoria.',
            'template_key.required' => 'El template es obligatorio.',
            'specialties.required'  => 'Tenés que seleccionar al menos una especialidad.',
        ]);

        // modalidad -> flags
        $mode_remote     = $data['modality'] === 'remoto' || $data['modality'] === 'ambas';
        $mode_presential = $data['modality'] === 'presencial' || $data['modality'] === 'ambas';

        // Si es presencial (o ambas), exigimos ciudad/provincia + address + lat/lng (para forzar selección del predictivo)
        if ($mode_presential) {
            $missing = [];
            if (empty($data['province_id']) || empty($data['province_name'])) $missing[] = 'Provincia';
            if (empty($data['city_id']) || empty($data['city_name'])) $missing[] = 'Ciudad';
            if (empty($data['address'])) $missing[] = 'Dirección';

            if (!empty($missing)) {
                return back()
                    ->withErrors(['general' => 'Faltan datos para modalidad presencial: ' . implode(', ', $missing) . '.'])
                    ->withInput();
            }

            if (!$this->addressHasNumber($data['address'] ?? null)) {
                return back()
                    ->withErrors(['address' => 'La dirección debe incluir calle y altura (por ejemplo: "Corrientes 1234").'])
                    ->withInput();
            }

            // Forzamos a que venga validada por el autocomplete (lat/lng)
            if (empty($data['lat']) || empty($data['lng'])) {
                return back()
                    ->withErrors(['address' => 'Tenés que elegir una sugerencia válida para validar la dirección.'])
                    ->withInput();
            }
        }

        // foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profiles', 'public');
        }

        // Geocoding + VALIDACIÓN ciudad/provincia
        $lat = null;
        $lng = null;

        if ($mode_presential) {
            // Preferimos coords del autocomplete
            $lat = (float) $data['lat'];
            $lng = (float) $data['lng'];

            // Validar que pertenezcan a la ciudad/provincia seleccionadas
            $ok = $this->coordsBelongToSelectedCity([
                'province_name' => $data['province_name'] ?? '',
                'city_name'     => $data['city_name'] ?? '',
            ], $lat, $lng);

            if (!$ok) {
                return back()
                    ->withErrors(['address' => 'La dirección ingresada no pertenece a la ciudad seleccionada. Elegí una sugerencia válida o cambiá la ciudad.'])
                    ->withInput();
            }

            // Compatibilidad extra: si por algún motivo vinieron coords vacías (aunque arriba las exigimos)
            // geocodificamos como fallback.
            if (!$lat || !$lng) {
                $geoQuery = trim((string)($data['address'] ?? '')) . ', ' .
                            trim((string)($data['city_name'] ?? '')) . ', ' .
                            trim((string)($data['province_name'] ?? '')) . ', Argentina';

                $geo = $this->geocodeAddress($geoQuery);

                if (!$geo) {
                    return back()
                        ->withErrors(['address' => 'No pudimos validar esa dirección en la ciudad seleccionada. Probá con otra más específica (calle y número).'])
                        ->withInput();
                }

                $lat = $geo['lat'];
                $lng = $geo['lng'];

                $ok2 = $this->coordsBelongToSelectedCity([
                    'province_name' => $data['province_name'] ?? '',
                    'city_name'     => $data['city_name'] ?? '',
                ], $lat, $lng);

                if (!$ok2) {
                    return back()
                        ->withErrors(['address' => 'La dirección ingresada no pertenece a la ciudad seleccionada. Verificá calle y número o elegí la ciudad correcta.'])
                        ->withInput();
                }
            }
        }

        // Compatibilidad con el resto del sitio actual
        $derivedState   = $data['province_name'] ?? null;
        $derivedCity    = $data['city_name'] ?? null;
        $derivedCountry = 'AR';

        // payload para aprobar
        $payload = [
            'display_name'    => $data['display_name'],
            'service_id'      => $data['service_id'] ?? null,
            'about'           => $data['about'] ?? null,
            'video_url'       => $data['video_url'] ?? null,
            'template_key'    => $data['template_key'],
            'mode_remote'     => $mode_remote,
            'mode_presential' => $mode_presential,

            // ids + nombres
            'province_id'     => $data['province_id'] ?? null,
            'province_name'   => $data['province_name'] ?? null,
            'city_id'         => $data['city_id'] ?? null,
            'city_name'       => $data['city_name'] ?? null,

            // Dirección + extra
            'address'         => $data['address'] ?? null,
            'address_extra'   => $data['address_extra'] ?? null,

            // compatibilidad / display actual
            'country'         => $derivedCountry,
            'state'           => $derivedState,
            'city'            => $derivedCity,

            // coords calculadas/validadas
            'lat'             => $lat,
            'lng'             => $lng,

            'whatsapp'        => $data['whatsapp'] ?? null,
            'contact_email'   => $data['contact_email'] ?? null,

            'specialties'     => $data['specialties'] ?? [],
        ];

        if ($photoPath) {
            $payload['photo_path'] = $photoPath;
        }

        Edit::create([
            'profile_id' => $profile->id,
            'payload'    => $payload,
            'status'     => 'pending',
        ]);

        return back()->with('status', 'Borrador enviado a revisión.');
    }

    public function cancelPending(Request $request)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->firstOrFail();

        $pending = Edit::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$pending) {
            return back()->with('status', 'No hay una petición pendiente.');
        }

        // limpiar foto subida si no es la actual del perfil
        $payload = is_array($pending->payload)
            ? $pending->payload
            : (json_decode($pending->payload, true) ?? []);

        if (!empty($payload['photo_path']) && $payload['photo_path'] !== $profile->photo_path) {
            try {
                Storage::disk('public')->delete($payload['photo_path']);
            } catch (\Throwable $e) {}
        }

        // Importante: respetamos el CHECK constraint (pending/approved/rejected)
        $pending->status      = 'rejected';
        $pending->reviewed_by = $user->id;
        $pending->reviewed_at = now();
        $pending->reason      = 'Cancelado por el usuario';
        $pending->save();

        return back()->with('status', 'Petición anulada. Ya podés editar tu perfil.');
    }
}
