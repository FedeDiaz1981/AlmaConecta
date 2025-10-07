<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProviderProfileController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\AdminEditController;

// Auth (Breeze/Fortify) controllers
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Diagnóstico mínimo
|--------------------------------------------------------------------------
*/
Route::get('/whoami', function () {
    if (!auth()->check()) return ['guest' => true];
    return auth()->user()->only(['id','email','role','account_status']);
})->name('whoami');

Route::middleware('auth')->get('/admin-test', function () {
    return [
        'user'         => auth()->user()->only(['email','role','account_status']),
        'allows_admin' => Gate::allows('admin'),
    ];
})->name('admin.test');

/**
 * Healthcheck para Render/monitoreo
 * - Respuesta JSON sin vistas
 */
Route::get('/healthz', function () {
    $db = 'down';
    $migrations = null;

    try {
        DB::select('select 1');
        $db = 'up';
        if (Schema::hasTable('migrations')) {
            $migrations = DB::table('migrations')->max('batch');
        }
    } catch (\Throwable $e) {
        Log::warning('Healthcheck DB error: '.$e->getMessage());
    }

    return response()->json([
        'ok'         => $db === 'up',
        'app_env'    => config('app.env'),
        'db'         => $db,
        'migrations' => $migrations,
    ], $db === 'up' ? 200 : 503);
})->name('healthz');

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
*/
Route::get('/', [SearchController::class, 'home'])->name('home');
Route::get('/search', [SearchController::class, 'search'])->name('search');

Route::get('/p/{slug}', [SearchController::class, 'show'])
    ->where('slug', '[^/]+') // acepta cualquier carácter excepto "/"
    ->name('profiles.show');

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
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Perfil profesional
    Route::get('/dashboard/profile',         [ProviderProfileController::class, 'edit'])->name('dashboard.profile.edit');
    Route::post('/dashboard/profile',        [ProviderProfileController::class, 'saveDraft'])->name('dashboard.profile.save');
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
| Auth (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas explícitas de login/register (solo si no existen)
|--------------------------------------------------------------------------
*/
if (!Route::has('login')) {
    Route::middleware('guest')->group(function () {
        Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    });
}

if (!Route::has('register')) {
    Route::middleware('guest')->group(function () {
        Route::get('/register',  [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store']);
    });
}

if (!Route::has('logout')) {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
}

/*
|--------------------------------------------------------------------------
| Fallback 404
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});
