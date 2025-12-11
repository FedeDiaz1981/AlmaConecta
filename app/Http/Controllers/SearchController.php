<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Profile;
use App\Models\Specialty;

class SearchController extends Controller
{
    public function home()
    {
        // Autocompletado: ahora toma los nombres de SPECIALTIES activas
        $serviceNames = Specialty::where('active', true)
            ->orderBy('name')
            ->pluck('name');

        // La vista sigue recibiendo $serviceNames para no romper JS existente
        return view('home', compact('serviceNames'));
    }

    /**
     * Geocodifica un lugar con Nominatim (OSM) y devuelve centro y bounding box.
     * Cachea 12 horas para no pegarle siempre al servicio.
     */
    protected function geocode(string $place): ?array
    {
        $cacheKey = 'geocode:' . md5($place);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($place) {
            $resp = Http::withHeaders([
                    'User-Agent' => 'alma/1.0 (+https://example.com)' // Nominatim pide UA identificable
                ])
                ->timeout(10)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $place,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                    'polygon_geojson' => 0,
                ]);

            if (!$resp->ok() || empty($resp[0])) {
                return null;
            }

            // Nominatim: boundingbox = [south, north, west, east]
            $r = $resp[0];

            return [
                'lat'  => (float) $r['lat'],
                'lng'  => (float) $r['lon'],
                'bbox' => [
                    'south' => (float) $r['boundingbox'][0],
                    'north' => (float) $r['boundingbox'][1],
                    'west'  => (float) $r['boundingbox'][2],
                    'east'  => (float) $r['boundingbox'][3],
                ],
            ];
        });
    }

    public function search(Request $request)
    {
        $q       = trim((string) $request->input('q', ''));
        $locText = trim((string) $request->input('loc', ''));
        $lat     = $request->filled('lat') ? (float) $request->input('lat') : null;
        $lng     = $request->filled('lng') ? (float) $request->input('lng') : null;
        $radius  = max(1, min(4000, (int) $request->input('r', 25))); // 1..4000 km
        $remote  = (bool) $request->boolean('remote', true);

        // Si vino texto de ubicación pero no coordenadas, geocodificamos
        $bbox = null;
        if (($lat === null || $lng === null) && $locText !== '') {
            if ($g = $this->geocode($locText)) {
                $lat  = $g['lat'];
                $lng  = $g['lng'];
                $bbox = $g['bbox'];
            }
        }

        // Seguridad adicional: si q no coincide con ninguna SPECIALTY activa, lo ignoramos
        if ($q !== '' && !Specialty::where('active', true)->where('name', $q)->exists()) {
            $q = '';
        }

        // Base de búsqueda
        $base = Profile::query()
            ->with('specialties')
            ->whereIn('status', ['approved', 'active'])
            ->when($q !== '', function ($query) use ($q) {
                // q matchea por nombre de SPECIALTY
                $query->whereHas('specialties', fn($s) => $s->where('name', $q));
            });

        // Si aún no tenemos centro => filtro simple por modalidad
        if ($lat === null || $lng === null) {
            $results = $base->when(
                $remote,
                fn ($qq) => $qq->where('mode_remote', true),
                fn ($qq) => $qq->where('mode_presential', true)
            )->latest('id')->paginate(20);

            return view('search.results', [
                'results' => $results,
                'q'       => $q,
                'loc'     => $locText,
                'lat'     => $lat,
                'lng'     => $lng,
                'r'       => $radius,
                'remote'  => $remote,
            ]);
        }

        // Construimos/expandimos bounding box en ±radius km
        if (!$bbox) {
            // bbox alrededor del centro
            $dLat = $radius / 111.32;
            $dLng = $radius / max(0.00001, (111.32 * cos(deg2rad($lat))));
            $bbox = [
                'south' => $lat - $dLat,
                'north' => $lat + $dLat,
                'west'  => $lng - $dLng,
                'east'  => $lng + $dLng,
            ];
        } else {
            // expandir bbox del geocoder
            $midLat = ($bbox['south'] + $bbox['north']) / 2.0;
            $dLat   = $radius / 111.32;
            $dLng   = $radius / max(0.00001, (111.32 * cos(deg2rad($midLat))));
            $bbox = [
                'south' => $bbox['south'] - $dLat,
                'north' => $bbox['north'] + $dLat,
                'west'  => $bbox['west']  - $dLng,
                'east'  => $bbox['east']  + $dLng,
            ];
        }

        /**
         * SI ESTAMOS EN SQLITE: no hay funciones trigonométricas (acos, radians, etc.)
         * Hacemos búsqueda simplificada: filtro por bbox + modalidad, sin cálculo de distancia.
         */
        if (DB::connection()->getDriverName() === 'sqlite') {
            $results = $base
                ->where(function ($w) use ($bbox, $remote) {
                    $w->where(function ($z) use ($bbox) {
                        $z->where('mode_presential', true)
                          ->whereNotNull('lat')->whereNotNull('lng')
                          ->whereBetween('lat', [$bbox['south'], $bbox['north']])
                          ->whereBetween('lng', [$bbox['west'],  $bbox['east']]);
                    });
                    if ($remote) {
                        $w->orWhere('mode_remote', true);
                    }
                })
                ->orderBy('id') // orden simple
                ->paginate(20);

            return view('search.results', [
                'results' => $results,
                'q'       => $q,
                'loc'     => $locText,
                'lat'     => $lat,
                'lng'     => $lng,
                'r'       => $radius,
                'remote'  => $remote,
            ]);
        }

        // --- Resto: sólo para motores que soportan trigonometría (MySQL/Postgres) ---

        // Expresión de distancia (Haversine con coseno esférico)
        $distExpr = "(6371 * acos( cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)) ))";

        // Pre-filtramos por bbox y calculamos distancia; incluimos remotos si corresponde
        $inner = (clone $base)
            ->select('profiles.*')
            ->selectRaw("$distExpr as distance", [$lat, $lng, $lat])
            ->where(function ($w) use ($bbox, $remote) {
                $w->where(function ($z) use ($bbox) {
                    $z->where('mode_presential', true)
                      ->whereNotNull('lat')->whereNotNull('lng')
                      ->whereBetween('lat', [$bbox['south'], $bbox['north']])
                      ->whereBetween('lng', [$bbox['west'],  $bbox['east']]);
                });
                if ($remote) {
                    $w->orWhere('mode_remote', true); // los remotos entran siempre
                }
            });

        // Ordenar por distancia en consulta exterior para que paginate() no rompa el alias
        $results = DB::query()
            ->fromSub($inner, 'p')
            ->orderByRaw('CASE WHEN p.distance IS NULL THEN 1 ELSE 0 END, p.distance ASC')
            ->paginate(20);

        return view('search.results', [
            'results' => $results,
            'q'       => $q,
            'loc'     => $locText,
            'lat'     => $lat,
            'lng'     => $lng,
            'r'       => $radius,
            'remote'  => $remote,
        ]);
    }

    public function show(string $slug, Request $request)
{
    $profile = Profile::with(['specialties'])
        ->where('slug', $slug)
        ->firstOrFail();

    // Dueño / admin / preview
    $isOwner = auth()->check() && auth()->id() === $profile->user_id;
    $isAdmin = auth()->check() && auth()->user()->can('admin');
    $preview = $request->boolean('preview');

    // Si NO está aprobado, solo lo pueden ver dueño o admin con ?preview=1
    if ($profile->status !== 'approved') {
        if (!($preview && ($isOwner || $isAdmin))) {
            abort(404);
        }
    }

    // Contador de vistas:
    // - No suma si es preview
    // - No suma si lo está viendo el dueño
    if (!$preview && !$isOwner) {
        $profile->increment('views_count');
    }

    return view('profiles.show', ['profile' => $profile]);
}

}
