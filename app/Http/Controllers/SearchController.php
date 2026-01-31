<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Models\Specialty;

class SearchController extends Controller
{
    public function home()
    {
        // Se mantiene por compatibilidad (aunque el JS nuevo ya no lo usa)
        $serviceNames = Specialty::where('active', true)
            ->orderBy('name')
            ->pluck('name');

        return view('home', compact('serviceNames'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        // Nueva fuente de verdad para ubicación:
        $provinceId   = trim((string) $request->input('province_id', ''));
        $provinceName = trim((string) $request->input('province_name', ''));
        $cityId       = trim((string) $request->input('city_id', ''));
        $cityName     = trim((string) $request->input('city_name', ''));

        // Flag remoto
        $remote = (bool) $request->boolean('remote', true);

        // Para no romper vistas viejas que esperan "loc/lat/lng/r"
        $locText = trim(implode(', ', array_filter([$cityName, $provinceName])));
        $lat = null;
        $lng = null;
        $radius = null;

        // Base de búsqueda
        $base = Profile::query()
            ->with('specialties')
            ->whereIn('status', ['approved', 'active']);

        // q: texto libre -> match parcial contra specialties.name (case-insensitive)
        if ($q !== '') {
            $needle = mb_strtolower($q);

            $base->whereHas('specialties', function ($s) use ($needle) {
                if (DB::getDriverName() === 'pgsql') {
                    $s->where('name', 'ILIKE', "%{$needle}%");
                } else {
                    $s->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]);
                }
            });
        }

        /**
         * Nueva lógica de ubicación:
         * - Si hay city_id: traer presenciales en esa ciudad.
         * - Si remote=1: sumar también todos los perfiles remotos (de cualquier ciudad).
         *
         * Nota: agrupamos con where(function) para que el OR no rompa el resto de filtros.
         */
        if ($cityId !== '') {
            $results = $base
                ->where(function ($w) use ($cityId, $provinceId, $remote) {
                    $w->where(function ($z) use ($cityId, $provinceId) {
                        $z->where('city_id', $cityId)
                          ->when($provinceId !== '', fn($qq) => $qq->where('province_id', $provinceId))
                          ->where('mode_presential', true);
                    });

                    if ($remote) {
                        $w->orWhere('mode_remote', true);
                    }
                })
                ->latest('id')
                ->paginate(20);

            return view('search.results', [
                'results' => $results,
                'q'       => $q,
                'loc'     => $locText,
                'lat'     => $lat,
                'lng'     => $lng,
                'r'       => $radius,
                'remote'  => $remote,

                // nuevos (por si la vista los quiere usar)
                'province_id'   => $provinceId,
                'province_name' => $provinceName,
                'city_id'       => $cityId,
                'city_name'     => $cityName,
            ]);
        }

        /**
         * Fallback (por si alguien entra a /search sin city_id):
         * - Si remote=1: mostrar remotos
         * - Si remote=0: mostrar presenciales (sin filtrar por ciudad)
         */
        $results = $base
            ->when(
                $remote,
                fn ($qq) => $qq->where('mode_remote', true),
                fn ($qq) => $qq->where('mode_presential', true)
            )
            ->latest('id')
            ->paginate(20);

        return view('search.results', [
            'results' => $results,
            'q'       => $q,
            'loc'     => $locText,
            'lat'     => $lat,
            'lng'     => $lng,
            'r'       => $radius,
            'remote'  => $remote,

            'province_id'   => $provinceId,
            'province_name' => $provinceName,
            'city_id'       => $cityId,
            'city_name'     => $cityName,
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
