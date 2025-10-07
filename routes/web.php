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
 * - No usa vistas ni blades (evita 500 por vistas)
 * - Devuelve info básica de DB y migrations
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

/**
 * IMPORTANTE: relajamos el patrón del slug para permitir acentos, guiones,
 * números, etc. (todo menos "/"). Antes el patrón ASCII podía causar 404.
 */
Route::get('/p/{slug}', [SearchController::class, 'show'])
    ->where('slug', '[^/]+')
    ->name('profiles.show');

/*
|--------------------------------------------------------------------------
| Dashboard
| - Admin  -> /admin
| - Provider -> edición de perfil
| - Otros -> dashboard básico
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
    Route::get('/dashboard/profile',        [ProviderProfileController::class, 'edit'])->name('dashboard.profile.edit');
    Route::post('/dashboard/profile',       [ProviderProfileController::class, 'saveDraft'])->name('dashboard.profile.save');
    Route::post('/dashboard/profile/cancel',[ProviderProfileController::class, 'cancelPending'])->name('dashboard.profile.cancel');
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
| Fallback 404 controlado (último)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    // Si tenés resources/views/errors/404.blade.php, Laravel la usa por defecto.
    // Si preferís, podés redirigir a home:
    // return redirect()->route('home');
    abort(404);
});
