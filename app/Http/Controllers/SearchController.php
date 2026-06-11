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
        if (!in_array($sort, ['rating_desc', 'name_desc'], true)) {
            $sort = 'rating_desc';
        }

        $featured = $request->boolean('featured', false);
        $all = $request->boolean('all', false);
        $searchMode = $request->input('search_mode') === 'zone' ? 'zone' : 'specialty';
        $selectedDynamicFilters = [
            'locations' => collect((array) $request->input('filter_locations', []))
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'specialties' => collect((array) $request->input('filter_specialties', []))
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'rating' => in_array($request->input('filter_rating'), ['excellent', 'very_good', 'good', 'low', 'unrated'], true)
                ? $request->input('filter_rating')
                : null,
            'modality' => in_array($request->input('filter_modality'), ['remote', 'presential', 'both'], true)
                ? $request->input('filter_modality')
                : null,
        ];

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
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
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
                return $query
                    ->orderBy('display_name', 'desc')
                    ->orderByDesc('id');
            }

            // default: relevancia = mejor rating
            if (DB::getDriverName() === 'pgsql') {
                return $query
                    ->orderByRaw('reviews_avg_rating DESC NULLS LAST')
                    ->orderByDesc('id');
            }

            return $query
                ->orderByDesc('reviews_avg_rating')
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

        $applyLocation = function ($query) use ($cityId, $provinceId, $remote) {
            if ($cityId !== '') {
                return $query->where(function ($w) use ($cityId, $provinceId, $remote) {
                    $w->where(function ($z) use ($cityId, $provinceId) {
                        $z->where('city_id', $cityId)
                          ->when($provinceId !== '', fn ($qq) => $qq->where('province_id', $provinceId))
                          ->where('mode_presential', true);
                    });

                    if ($remote) {
                        $w->orWhere('mode_remote', true);
                    }
                });
            }

            if ($provinceId !== '') {
                return $query->where(function ($w) use ($provinceId, $remote) {
                    $w->where(function ($z) use ($provinceId) {
                        $z->where('province_id', $provinceId)
                          ->where('mode_presential', true);
                    });

                    if ($remote) {
                        $w->orWhere('mode_remote', true);
                    }
                });
            }

            if ($remote) {
                return $query;
            }

            return $query->where('mode_presential', true);
        };

        if (!$featured && !$all) {
            $base = $applyLocation($base);
        }

        $baseTotal = (clone $base)->count();
        $dynamicFilters = $this->buildDynamicFilters($base, $baseTotal, $selectedDynamicFilters);

        $query = clone $base;
        if ($dynamicFilters['selected_ids'] !== null) {
            $ids = $dynamicFilters['selected_ids'];
            $query->whereIn('profiles.id', $ids !== [] ? $ids : [0]);
        }

        $results = $applySort($query)->paginate($perPage);

        return view('search.results', [
            'results' => $results,
            'q'       => $q,
            'word'    => $word,
            'sort'    => $sort,
            'featured' => $featured,
            'all'     => $all,
            'loc'     => $locText,
            'lat'     => $lat,
            'lng'     => $lng,
            'r'       => $radius,
            'remote'  => $remote,
            'search_mode' => $searchMode,
            'dynamicFilters' => $dynamicFilters,
            'selectedDynamicFilters' => $selectedDynamicFilters,

            'province_id'   => $provinceId,
            'province_name' => $provinceName,
            'city_id'       => $cityId,
            'city_name'     => $cityName,
        ]);
    }

    private function buildDynamicFilters($query, int $baseTotal, array $selected): array
    {
        $activeCount = $this->activeDynamicFilterCount($selected);
        $empty = [
            'enabled' => false,
            'base_total' => $baseTotal,
            'has_groups' => false,
            'active_count' => $activeCount,
            'locations' => [],
            'specialties' => [],
            'ratings' => [],
            'modalities' => [],
            'selected_ids' => null,
        ];

        if ($baseTotal <= 10 && $activeCount === 0) {
            return $empty;
        }

        $profiles = (clone $query)->get();

        $locations = $profiles
            ->map(fn (Profile $profile) => $this->locationFacet($profile))
            ->filter()
            ->groupBy('key')
            ->map(fn ($items, $key) => [
                'key' => $key,
                'label' => $items->first()['label'],
                'count' => $items->count(),
                'selected' => in_array($key, $selected['locations'], true),
            ])
            ->sortBy([
                ['count', 'desc'],
                ['label', 'asc'],
            ])
            ->take(12)
            ->values()
            ->all();

        $specialties = $profiles
            ->flatMap(fn (Profile $profile) => $profile->specialties->map(fn ($specialty) => [
                'id' => (int) $specialty->id,
                'name' => $specialty->name,
            ]))
            ->groupBy('id')
            ->map(fn ($items, $id) => [
                'id' => (int) $id,
                'label' => $items->first()['name'],
                'count' => $items->count(),
                'selected' => in_array((int) $id, $selected['specialties'], true),
            ])
            ->sortBy([
                ['count', 'desc'],
                ['label', 'asc'],
            ])
            ->take(12)
            ->values()
            ->all();

        $ratingLabels = [
            'excellent' => '4.5 o más',
            'very_good' => '4.0 a 4.4',
            'good' => '3.0 a 3.9',
            'low' => 'Menos de 3.0',
            'unrated' => 'Sin calificación',
        ];

        $ratings = $profiles
            ->map(fn (Profile $profile) => $this->ratingFacetKey($profile->reviews_avg_rating))
            ->countBy()
            ->map(fn ($count, $key) => [
                'key' => $key,
                'label' => $ratingLabels[$key] ?? $key,
                'count' => $count,
                'selected' => $selected['rating'] === $key,
            ])
            ->sortBy(fn ($item) => array_search($item['key'], array_keys($ratingLabels), true))
            ->values()
            ->all();

        $modalityLabels = [
            'remote' => 'Online/remota',
            'presential' => 'Presencial',
            'both' => 'Ambas',
        ];

        $modalities = $profiles
            ->map(fn (Profile $profile) => $this->modalityFacetKey($profile))
            ->filter()
            ->countBy()
            ->map(fn ($count, $key) => [
                'key' => $key,
                'label' => $modalityLabels[$key] ?? $key,
                'count' => $count,
                'selected' => $selected['modality'] === $key,
            ])
            ->sortBy(fn ($item) => array_search($item['key'], array_keys($modalityLabels), true))
            ->values()
            ->all();

        $filteredProfiles = $profiles;

        if ($selected['locations'] !== []) {
            $filteredProfiles = $filteredProfiles->filter(function (Profile $profile) use ($selected) {
                $facet = $this->locationFacet($profile);

                return $facet && in_array($facet['key'], $selected['locations'], true);
            });
        }

        if ($selected['specialties'] !== []) {
            $filteredProfiles = $filteredProfiles->filter(fn (Profile $profile) =>
                $profile->specialties->pluck('id')->map(fn ($id) => (int) $id)->intersect($selected['specialties'])->isNotEmpty()
            );
        }

        if ($selected['rating']) {
            $filteredProfiles = $filteredProfiles->filter(fn (Profile $profile) =>
                $this->ratingFacetKey($profile->reviews_avg_rating) === $selected['rating']
            );
        }

        if ($selected['modality']) {
            $filteredProfiles = $filteredProfiles->filter(fn (Profile $profile) =>
                $this->modalityFacetKey($profile) === $selected['modality']
            );
        }

        return [
            'enabled' => $baseTotal > 10,
            'base_total' => $baseTotal,
            'has_groups' => $baseTotal > 10 && (count($locations) > 1 || count($specialties) > 1 || count($ratings) > 1 || count($modalities) > 1),
            'active_count' => $activeCount,
            'locations' => count($locations) > 1 ? $locations : [],
            'specialties' => count($specialties) > 1 ? $specialties : [],
            'ratings' => count($ratings) > 1 ? $ratings : [],
            'modalities' => count($modalities) > 1 ? $modalities : [],
            'selected_ids' => $activeCount > 0 ? $filteredProfiles->pluck('id')->values()->all() : null,
        ];
    }

    private function activeDynamicFilterCount(array $selected): int
    {
        return count($selected['locations'])
            + count($selected['specialties'])
            + ($selected['rating'] ? 1 : 0)
            + ($selected['modality'] ? 1 : 0);
    }

    private function locationFacet(Profile $profile): ?array
    {
        $city = trim((string) ($profile->city_name ?: $profile->city));
        $state = trim((string) ($profile->province_name ?: $profile->state));

        if ($city === '' && $state === '') {
            return null;
        }

        $payload = [
            'city_id' => (string) ($profile->city_id ?? ''),
            'province_id' => (string) ($profile->province_id ?? ''),
            'city' => $city,
            'state' => $state,
        ];

        $label = trim(implode(', ', array_filter([$city, $state])));
        $key = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        return [
            'key' => $key,
            'label' => $label,
        ];
    }

    private function ratingFacetKey($avgRating): string
    {
        if ($avgRating === null) {
            return 'unrated';
        }

        $rating = (float) $avgRating;

        if ($rating >= 4.5) {
            return 'excellent';
        }

        if ($rating >= 4.0) {
            return 'very_good';
        }

        if ($rating >= 3.0) {
            return 'good';
        }

        return 'low';
    }

    private function modalityFacetKey(Profile $profile): ?string
    {
        if ($profile->mode_remote && $profile->mode_presential) {
            return 'both';
        }

        if ($profile->mode_remote) {
            return 'remote';
        }

        if ($profile->mode_presential) {
            return 'presential';
        }

        return null;
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
