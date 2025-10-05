<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProviderProfileController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\AdminEditController;

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

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
*/
Route::get('/', [SearchController::class, 'home'])->name('home');
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/p/{slug}', [SearchController::class, 'show'])->name('profiles.show');

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
        // Eliminar usuario (botón "Eliminar" del dashboard)
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
