<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoRefController extends Controller
{
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
     * Helper: obtiene (y cachea) el bounding box de una ciudad/provincia usando Nominatim.
     * Return: ['south'=>..,'north'=>..,'west'=>..,'east'=>..] | null
     */
    protected function getCityBbox(string $cityName, string $provinceName): ?array
    {
        $cityName = trim($cityName);
        $provinceName = trim($provinceName);

        if ($cityName === '' || $provinceName === '') return null;

        $client = $this->httpClient();

        $bboxKey = 'nominatim:bbox:v1:' . md5($cityName . '|' . $provinceName);

        return Cache::remember($bboxKey, now()->addDays(30), function () use ($client, $cityName, $provinceName) {
            $res = $client->withHeaders([
                    'User-Agent' => 'alma-conecta/1.0 (+https://example.com)',
                ])
                ->timeout(12)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $cityName . ', ' . $provinceName . ', Argentina',
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                    'accept-language' => 'es',
                    'countrycodes' => 'ar',
                ]);

            if (!$res->ok()) return null;

            $data = $res->json();
            if (!is_array($data) || empty($data[0]['boundingbox'])) return null;

            // boundingbox = [south, north, west, east]
            $bb = $data[0]['boundingbox'];

            $south = (float) ($bb[0] ?? 0);
            $north = (float) ($bb[1] ?? 0);
            $west  = (float) ($bb[2] ?? 0);
            $east  = (float) ($bb[3] ?? 0);

            // sanity check
            if (!$south || !$north || !$west || !$east) return null;

            return [
                'south' => $south,
                'north' => $north,
                'west'  => $west,
                'east'  => $east,
            ];
        });
    }

    /**
     * GET /geo/provincias
     * Output: { items: [ {id, nombre}, ... ] }
     */
    public function provincias()
    {
        $items = Cache::remember('georef_provincias_v3', now()->addDays(7), function () {
            $client = $this->httpClient();

            $res = $client->timeout(10)->get('https://apis.datos.gob.ar/georef/api/provincias', [
                'aplanar' => true,
                'campos'  => 'id,nombre',
                'max'     => 200,
                'orden'   => 'nombre',
            ]);

            if (!$res->ok()) {
                return [];
            }

            $data = $res->json();

            return collect($data['provincias'] ?? [])
                ->map(fn ($p) => [
                    'id'     => (string)($p['id'] ?? ''),
                    'nombre' => (string)($p['nombre'] ?? ''),
                ])
                ->filter(fn ($p) => $p['id'] !== '' && $p['nombre'] !== '')
                ->values()
                ->all();
        });

        return response()->json(['items' => $items], 200);
    }

    /**
     * GET /geo/ciudades?provincia=<id>
     * Output: { items: [ {id, nombre}, ... ] }
     */
    public function ciudades(Request $request)
    {
        $provincia = trim((string) $request->query('provincia', ''));

        if ($provincia === '') {
            return response()->json(['items' => []], 200);
        }

        $cacheKey = 'georef_ciudades_v3_' . md5($provincia);

        $items = Cache::remember($cacheKey, now()->addDays(7), function () use ($provincia) {
            $client = $this->httpClient();

            $res = $client->timeout(15)->get('https://apis.datos.gob.ar/georef/api/localidades', [
                'aplanar'   => true,
                'provincia' => $provincia,
                'campos'    => 'id,nombre',
                'max'       => 5000,
                'orden'     => 'nombre',
            ]);

            if (!$res->ok()) {
                return [];
            }

            $data = $res->json();

            return collect($data['localidades'] ?? [])
                ->map(fn ($c) => [
                    'id'     => (string)($c['id'] ?? ''),
                    'nombre' => (string)($c['nombre'] ?? ''),
                ])
                ->filter(fn ($c) => $c['id'] !== '' && $c['nombre'] !== '')
                ->values()
                ->all();
        });

        return response()->json(['items' => $items], 200);
    }

    /**
     * GET /geo/address-suggest?city_name=...&province_name=...&q=...
     * Output: { items: [ {label, lat, lng}, ... ] }
     *
     * - Acota sugerencias al bounding box de la ciudad (Nominatim).
     * - Devuelve solo "calle + altura" (road + house_number).
     */
    public function addressSuggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $cityName = trim((string) $request->query('city_name', ''));
        $provinceName = trim((string) $request->query('province_name', ''));

        // 🔥 clave: recién sugerimos cuando hay calle + altura (al menos 3 chars total, y el front lo llama con número)
        if (mb_strlen($q) < 3 || $cityName === '' || $provinceName === '') {
            return response()->json(['items' => []], 200);
        }

        $cacheKey = 'nominatim:addr:v2:' . md5($q . '|' . $cityName . '|' . $provinceName);

        $items = Cache::remember($cacheKey, now()->addHours(6), function () use ($q, $cityName, $provinceName) {
            $client = $this->httpClient();
            $bbox = $this->getCityBbox($cityName, $provinceName);

            $baseParams = [
                'format' => 'json',
                'limit' => 10,
                'addressdetails' => 1,
                'accept-language' => 'es',
                'countrycodes' => 'ar',
            ];

            $queries = [];

            if ($bbox) {
                $viewbox = implode(',', [$bbox['west'], $bbox['north'], $bbox['east'], $bbox['south']]);
                $queries[] = array_merge($baseParams, [
                    'street' => $q,
                    'city' => $cityName,
                    'state' => $provinceName,
                    'country' => 'Argentina',
                    'viewbox' => $viewbox,
                    'bounded' => 1,
                ]);
            }

            $queries[] = array_merge($baseParams, [
                'q' => $q . ', ' . $cityName . ', ' . $provinceName . ', Argentina',
            ]);

            $data = [];
            foreach ($queries as $params) {
                $res = $client->withHeaders([
                        'User-Agent' => 'alma-conecta/1.0 (+https://example.com)',
                    ])
                    ->timeout(12)
                    ->get('https://nominatim.openstreetmap.org/search', $params);

                if ($res->ok()) {
                    $data = $res->json();
                    if (is_array($data) && !empty($data)) {
                        break;
                    }
                }
            }

            if (!is_array($data)) {
                return [];
            }

            return collect($data)
                ->map(function ($r) {
                    $a = $r['address'] ?? [];

                    $road = $a['road'] ?? ($a['pedestrian'] ?? ($a['path'] ?? ''));
                    $hn   = $a['house_number'] ?? '';

                    if ((!$road || !$hn) && !empty($r['display_name'])) {
                        if (preg_match('/^(.*?)[,]?\s+(\d[\d\w\-\/]*)/u', $r['display_name'], $m)) {
                            $road = $road ?: trim($m[1]);
                            $hn = $hn ?: trim($m[2]);
                        }
                    }

                    if (!$road || !$hn) return null;

                    $label = trim($road . ' ' . $hn);

                    $lat = isset($r['lat']) ? (float) $r['lat'] : null;
                    $lng = isset($r['lon']) ? (float) $r['lon'] : null;

                    if (!$label || !$lat || !$lng) return null;

                    return [
                        'label' => $label,
                        'lat'   => $lat,
                        'lng'   => $lng,
                    ];
                })
                ->filter()
                ->unique('label')
                ->values()
                ->all();
        });

        return response()->json(['items' => $items], 200);
    }

    /**
     * GET /geo/street-suggest?city_name=...&province_name=...&q=...
     * Output: { items: [ {label}, ... ] }
     *
     * - Devuelve sugerencias de calles dentro del bounding box de la ciudad.
     */
    public function streetSuggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $cityName = trim((string) $request->query('city_name', ''));
        $provinceName = trim((string) $request->query('province_name', ''));

        if (mb_strlen($q) < 2 || $cityName === '' || $provinceName === '') {
            return response()->json(['items' => []], 200);
        }

        $cacheKey = 'nominatim:street:v1:' . md5($q . '|' . $cityName . '|' . $provinceName);

        $items = Cache::remember($cacheKey, now()->addDays(7), function () use ($q, $cityName, $provinceName) {
            $client = $this->httpClient();

            $bbox = $this->getCityBbox($cityName, $provinceName);
            $viewbox = $bbox ? implode(',', [$bbox['west'], $bbox['north'], $bbox['east'], $bbox['south']]) : null;

            $params = [
                'format' => 'json',
                'limit' => 10,
                'addressdetails' => 1,
                'accept-language' => 'es',
                'countrycodes' => 'ar',
                'q' => $q . ', ' . $cityName . ', ' . $provinceName,
            ];

            if ($viewbox) {
                $params['viewbox'] = $viewbox;
                $params['bounded'] = 1;
            }

            $res = $client->withHeaders([
                    'User-Agent' => 'alma-conecta/1.0 (+https://example.com)',
                ])
                ->timeout(12)
                ->get('https://nominatim.openstreetmap.org/search', $params);

            if (!$res->ok()) {
                return [];
            }

            $data = $res->json();
            if (!is_array($data)) {
                return [];
            }

            return collect($data)
                ->map(function ($r) {
                    $a = $r['address'] ?? [];
                    $road = $a['road'] ?? ($a['pedestrian'] ?? ($a['path'] ?? ''));

                    if (!$road) return null;

                    return [
                        'label' => trim($road),
                    ];
                })
                ->filter()
                ->unique('label')
                ->values()
                ->all();
        });

        return response()->json(['items' => $items], 200);
    }

    /**
     * GET /geo/street-preload?city_name=...&province_name=...
     * Precalienta cache (bbox de la ciudad) para acelerar sugerencias.
     */
    public function streetPreload(Request $request)
    {
        $cityName = trim((string) $request->query('city_name', ''));
        $provinceName = trim((string) $request->query('province_name', ''));

        if ($cityName === '' || $provinceName === '') {
            return response()->json(['ok' => false], 200);
        }

        $bbox = $this->getCityBbox($cityName, $provinceName);

        return response()->json(['ok' => (bool) $bbox], 200);
    }
}
