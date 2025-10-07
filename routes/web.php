<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProviderProfileController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\AdminEditController;

/*
|--------------------------------------------------------------------------
| Helper para evitar 500 silenciosos
|--------------------------------------------------------------------------
*/
$safe = function ($action) {
    try {
        return $action();
    } catch (\Throwable $e) {
        report($e);

        if (config('app.debug')) {
            return response('Internal error: '.$e->getMessage(), 500);
        }
        return response('Service temporarily unavailable', 503);
    }
};

/*
|--------------------------------------------------------------------------
| Diagnóstico
|--------------------------------------------------------------------------
*/
Route::get('/whoami', fn () => auth()->check()
    ? auth()->user()->only(['id','email','role','account_status'])
    : ['guest' => true]);

Route::middleware('auth')->get('/admin-test', fn () => [
    'user'          => auth()->user()->only(['email','role','account_status']),
    'allows_admin'  => Gate::allows('admin'),
]);

// Healthcheck para Render/monitoreo
Route::get('/healthz', function () {
    $db = 'down';
    $migrations = null;
    $pivotId = null;
    $pivotCount = null;

    try {
        DB::select('select 1');
        $db = 'up';

        if (Schema::hasTable('migrations')) {
            $migrations = DB::table('migrations')->max('batch');
        }

        if (Schema::hasTable('profile_service')) {
            $pivotId = Schema::hasColumn('profile_service', 'id') ? 'present' : 'missing';
            try {
                $pivotCount = DB::table('profile_service')->count();
            } catch (\Throwable $e) {
                $pivotCount = 'error: '.$e->getMessage();
            }
        } else {
            $pivotId = 'table-missing';
        }
    } catch (\Throwable $e) {
        Log::warning('Healthcheck DB error: '.$e->getMessage());
    }

    return response()->json([
        'ok'           => $db === 'up',
        'app_env'      => config('app.env'),
        'db'           => $db,
        'migrations'   => $migrations,
        'pivot_id'     => $pivotId,
        'pivot_count'  => $pivotCount,
    ], $db === 'up' ? 200 : 503);
});

// Ver último log de Laravel (solo en debug)
Route::get('/__log', function () {
    if (!config('app.debug')) {
        abort(404);
    }
    $path = storage_path('logs/laravel.log');
    if (!is_file($path)) {
        return response("No hay laravel.log aún.", 200);
    }
    $content = @file_get_contents($path);
    $tail = Str::of($content)->substr(-20000);
    return response("<pre>".e($tail)."</pre>", 200)->header('Content-Type', 'text/html');
});

// Diagnóstico del pivot (solo lectura)
Route::get('/diag/pivot', function () use ($safe) {
    return $safe(function () {
        if (!Schema::hasTable('profile_service')) {
            return response()->json(['exists' => false], 200);
        }
        return response()->json([
            'exists'     => true,
            'has_id_col' => Schema::hasColumn('profile_service', 'id'),
            'sample'     => DB::table('profile_service')->limit(5)->get(['profile_id','service_id']),
        ], 200);
    });
});

/*
|--------------------------------------------------------------------------
| *** Ruta temporal para crear/promover ADMIN (opción A) ***
| Requiere envs: ADMIN_SEED_TOKEN, ADMIN_EMAIL, ADMIN_PASSWORD
| Usar 1 vez y luego borrar esta ruta del código.
|--------------------------------------------------------------------------
*/
Route::get('/__seed-admin', function (Request $r) use ($safe) {
    return $safe(function () use ($r) {
        $token    = $r->query('token');
        $expected = env('ADMIN_SEED_TOKEN');
        abort_unless($token && $expected && hash_equals($expected, $token), 403, 'Forbidden');

        $email = env('ADMIN_EMAIL');
        $pass  = env('ADMIN_PASSWORD');
        abort_unless($email && $pass, 422, 'Faltan ADMIN_EMAIL o ADMIN_PASSWORD');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => 'Admin',
                'password'          => Hash::make($pass),
                'role'              => 'admin',
                'account_status'    => 'active',
                'email_verified_at' => now(),
            ]
        );

        return response()->json([
            'ok'    => true,
            'id'    => $user->id,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    });
})->middleware('throttle:3,1');

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
|
| Tip: si BYPASS_HOME=1 (env), devolvemos una página mínima para aislar
| errores del SearchController/blade mientras depurás.
|
*/
Route::get('/', function () use ($safe) {
    if (env('BYPASS_HOME', false)) {
        try {
            $now = DB::select('select now() as now');
            $dbNow = $now[0]->now ?? null;
        } catch (\Throwable $e) {
            $dbNow = 'db-error: '.$e->getMessage();
        }

        return response()->view('welcome', [
            'status' => 'OK',
            'db_now' => $dbNow,
        ], 200);
    }

    return $safe(fn () => app(SearchController::class)->home(request()));
})->name('home');

// Búsqueda (GET)
Route::get('/search', function () use ($safe) {
    return $safe(fn () => app(SearchController::class)->search(request()));
})->name('search');

// Perfil público por slug (pasando también el Request)
Route::get('/p/{slug}', function (string $slug) use ($safe) {
    return $safe(fn () => app(SearchController::class)->show(request(), $slug));
})->where('slug', '[A-Za-z0-9\-]+')->name('profiles.show');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $u = auth()->user();

    if (Gate::allows('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($u && (($u->role ?? null) === 'provider' || ($u->is_provider ?? false))) {
        return redirect()->route('dashboard.profile.edit');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Área autenticada
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Perfil de cuenta
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Perfil profesional
    Route::get('/dashboard/profile', [ProviderProfileController::class, 'edit'])->name('dashboard.profile.edit');
    Route::post('/dashboard/profile', [ProviderProfileController::class, 'saveDraft'])->name('dashboard.profile.save');
    Route::post('/dashboard/profile/cancel', [ProviderProfileController::class, 'cancelPending'])->name('dashboard.profile.cancel');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'can:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [UserApprovalController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/approve',  [UserApprovalController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject',   [UserApprovalController::class, 'reject'])->name('users.reject');
        Route::post('/users/{user}/suspend',  [UserApprovalController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/activate', [UserApprovalController::class, 'activate'])->name('users.activate');
        Route::delete('/users/{user}',        [UserApprovalController::class, 'destroy'])->name('users.destroy');

        Route::get('/edits', [AdminEditController::class, 'index'])->name('edits.index');
        Route::post('/edits/{edit}/approve', [AdminEditController::class, 'approve'])->name('edits.approve');
        Route::post('/edits/{edit}/reject',  [AdminEditController::class, 'reject'])->name('edits.reject');
    });

/*
|--------------------------------------------------------------------------
| Auth (Breeze/Laravel)
|--------------------------------------------------------------------------
| Aquí se registran: GET /login, POST /login, GET /register, POST /register,
| POST /logout, Forgot Password, etc.
*/
require __DIR__ . '/auth.php';
