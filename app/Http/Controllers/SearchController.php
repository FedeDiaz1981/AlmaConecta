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
        $word = trim((string) $request->input('word', ''));
        $sort = trim((string) $request->input('sort', 'rating_desc'));
        $featured = $request->boolean('featured', false);
        $all = $request->boolean('all', false);

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
            ->whereIn('status', ['approved', 'active'])
            ->where('is_suspended', false);

        if ($featured) {
            $base->whereHas('reviews');
        }

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

        // Filtro extra por palabra (nombre, descripción o especialidades)
        if ($word !== '') {
            $needleWord = mb_strtolower($word);

            $base->where(function ($w) use ($needleWord) {
                if (DB::getDriverName() === 'pgsql') {
                    $w->where('display_name', 'ILIKE', "%{$needleWord}%")
                      ->orWhere('about', 'ILIKE', "%{$needleWord}%")
                      ->orWhereHas('specialties', function ($s) use ($needleWord) {
                          $s->where('name', 'ILIKE', "%{$needleWord}%");
                      });
                } else {
                    $w->whereRaw('LOWER(display_name) LIKE ?', ["%{$needleWord}%"])
                      ->orWhereRaw('LOWER(about) LIKE ?', ["%{$needleWord}%"])
                      ->orWhereHas('specialties', function ($s) use ($needleWord) {
                          $s->whereRaw('LOWER(name) LIKE ?', ["%{$needleWord}%"]);
                      });
                }
            });
        }

        $applySort = function ($query) use ($sort) {
            if ($sort === 'name_desc') {
                return $query->orderBy('display_name', 'desc');
            }

            // default: relevancia = mejor rating
            return $query
                ->withAvg('reviews', 'rating')
                ->orderByRaw('COALESCE(reviews_avg_rating, 0) DESC')
                ->orderByDesc('id');
        };

        /**
         * Nueva lógica de ubicación:
         * - Si hay city_id: traer presenciales en esa ciudad.
         * - Si remote=1: sumar también todos los perfiles remotos (de cualquier ciudad).
         *
         * Nota: agrupamos con where(function) para que el OR no rompa el resto de filtros.
         */
        $perPage = $featured ? 10 : 15;

        if ($featured) {
            $results = $applySort($base)
                ->paginate($perPage);

            return view('search.results', [
                'results' => $results,
                'q'       => $q,
                'word'    => $word,
                'sort'    => $sort,
                'featured' => true,
                'all'     => $all,
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

        if ($all) {
            $results = $applySort($base)
                ->paginate($perPage);

            return view('search.results', [
                'results' => $results,
                'q'       => $q,
                'word'    => $word,
                'sort'    => $sort,
                'featured' => false,
                'all'     => true,
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

        if ($cityId !== '') {
            $results = $applySort($base
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
            )->paginate($perPage);

            return view('search.results', [
                'results' => $results,
                'q'       => $q,
                'word'    => $word,
                'sort'    => $sort,
                'loc'     => $locText,
                'lat'     => $lat,
                'lng'     => $lng,
                'r'       => $radius,
                'remote'  => $remote,
                'featured' => false,
                'all'     => false,

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
        $results = $applySort($base
            ->when(
                $remote,
                fn ($qq) => $qq->where('mode_remote', true),
                fn ($qq) => $qq->where('mode_presential', true)
            )
        )->paginate($perPage);

        return view('search.results', [
            'results' => $results,
            'q'       => $q,
            'word'    => $word,
            'sort'    => $sort,
            'featured' => false,
            'all'     => false,
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
        $profile = Profile::with(['specialties', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Dueño / admin / preview
        $isOwner = auth()->check() && auth()->id() === $profile->user_id;
        $isAdmin = auth()->check() && auth()->user()->can('admin');
        $preview = $request->boolean('preview');

        if ($profile->is_suspended) {
            if (!$isAdmin) {
                abort(404);
            }
        }

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

        $reviewsCount = $profile->reviews->count();
        $avgRating = $reviewsCount ? round($profile->reviews->avg('rating'), 1) : null;

        $userReview = null;
        if (auth()->check() && (auth()->user()->role ?? null) === 'client') {
            $userReview = $profile->reviews->firstWhere('user_id', auth()->id());
        }

        return view('profiles.show', [
            'profile' => $profile,
            'avgRating' => $avgRating,
            'reviewsCount' => $reviewsCount,
            'userReview' => $userReview,
        ]);
    }
}
