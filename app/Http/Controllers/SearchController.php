<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Profile;

class SearchController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function search(Request $request)
    {
        $q       = trim((string) $request->input('q', ''));
        $locText = trim((string) $request->input('loc', ''));
        $lat     = $request->filled('lat') ? (float) $request->input('lat') : null;
        $lng     = $request->filled('lng') ? (float) $request->input('lng') : null;
        $radius  = max(1, (int) $request->input('r', 25));
        $remote  = (bool) $request->boolean('remote', true);

        // Base
        $base = Profile::query()
            ->with('service')
            // admití "approved" y también "active" por compatibilidad
            ->whereIn('status', ['approved', 'active'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('display_name', 'like', "%{$q}%")
                      ->orWhereHas('service', fn ($s) => $s->where('name', 'like', "%{$q}%"));
                });
            });

        // SIN centro -> filtro simple por modalidad
        if ($lat === null || $lng === null) {
            $results = $base->when(
                $remote,
                fn ($q) => $q->where('mode_remote', true),
                fn ($q) => $q->where('mode_presential', true)
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

        // CON centro
        if (DB::getDriverName() !== 'sqlite') {
            // --- PostgreSQL/MySQL: usar trigonometría en SQL ---
            // OJO: sin paréntesis de más
            $distanceExpr = "(6371 * acos( cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)) ))";

            $profiles = (clone $base)
                ->select('profiles.*')
                ->selectRaw("$distanceExpr as distance", [$lat, $lng, $lat])
                ->where(function ($w) use ($distanceExpr, $lat, $lng, $radius, $remote) {
                    $w->where(function ($z) use ($distanceExpr, $lat, $lng, $radius) {
                        $z->where('mode_presential', true)
                          ->whereNotNull('lat')->whereNotNull('lng')
                          ->whereRaw("$distanceExpr <= ?", [$lat, $lng, $lat, $radius]);
                    });
                    if ($remote) {
                        $w->orWhere('mode_remote', true);
                    }
                })
                ->orderByRaw('CASE WHEN distance IS NULL THEN 1 ELSE 0 END, distance ASC');

            $results = $profiles->paginate(20);
        } else {
            // --- SQLITE: calcular distancia en PHP ---
            $presentials = (clone $base)
                ->where('mode_presential', true)
                ->whereNotNull('lat')->whereNotNull('lng')
                ->get();

            $haversine = function ($lat1, $lon1, $lat2, $lon2) {
                $R = 6371;
                $dLat = deg2rad($lat2 - $lat1);
                $dLon = deg2rad($lon2 - $lon1);
                $a = sin($dLat / 2) ** 2
                   + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
                $c = 2 * asin(min(1, sqrt($a)));
                return $R * $c;
            };

            $presentials->transform(function ($p) use ($lat, $lng, $haversine) {
                $p->distance = $haversine($lat, $lng, (float) $p->lat, (float) $p->lng);
                return $p;
            });

            $presentials = $presentials->filter(fn ($p) => $p->distance <= $radius);

            $remotes = $remote
                ? (clone $base)->where('mode_remote', true)->get()->each(function ($p) {
                    $p->distance = null;
                })
                : collect();

            $collection = $presentials->concat($remotes)->unique('id')
                ->sortBy(fn ($p) => $p->distance === null ? PHP_INT_MAX : $p->distance)
                ->values();

            // Paginar manualmente
            $page    = Paginator::resolveCurrentPage('page');
            $perPage = 20;
            $total   = $collection->count();
            $items   = $collection->forPage($page, $perPage)->values();
            $results = new LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
            ]);
        }

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

    public function show(string $slug, \Illuminate\Http\Request $request)
    {
        $profile = Profile::with(['service'])
            ->where('slug', $slug)
            ->firstOrFail();

        if ($profile->status !== 'approved') {
            $isOwner = auth()->check() && auth()->id() === $profile->user_id;
            $isAdmin = auth()->check() && auth()->user()->can('admin');
            $preview = $request->boolean('preview');

            if (!($preview && ($isOwner || $isAdmin))) {
                abort(404);
            }
        }

        return view('profiles.show', ['profile' => $profile]);
    }
}
