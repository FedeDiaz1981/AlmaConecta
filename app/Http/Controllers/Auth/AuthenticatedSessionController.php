<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostrar vista de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Iniciar sesión.
     * Bloquea cuentas con account_status = suspended | deleted.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Log::info('login.store start', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'session_id' => $request->session()->getId(),
        ]);

        // Pre-chequeo: si la cuenta está suspendida/eliminada, no intentamos autenticar
        $user = User::where('email', $request->input('email'))->first();
        if ($user && in_array($user->account_status, ['suspended', 'deleted'], true)) {
            Log::warning('login blocked by account_status', [
                'email' => $user->email,
                'account_status' => $user->account_status,
            ]);
            throw ValidationException::withMessages([
                'email' => $user->account_status === 'suspended'
                    ? 'Tu cuenta está suspendida. Contactá al administrador.'
                    : 'Tu cuenta fue eliminada.',
            ]);
        }

        // Autenticar credenciales (Breeze)
        $request->authenticate();
        Log::info('login.authenticate passed', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => $request->session()->getId(),
        ]);

        // Post-chequeo defensivo: si entre el intento y ahora se suspendió/eliminó
        $authUser = Auth::user();
        if ($authUser && in_array($authUser->account_status, ['suspended', 'deleted'], true)) {
            Log::warning('login blocked after authenticate', [
                'user_id' => $authUser->id,
                'account_status' => $authUser->account_status,
            ]);
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => $authUser->account_status === 'suspended'
                    ? 'Tu cuenta está suspendida. Contactá al administrador.'
                    : 'Tu cuenta fue eliminada.',
            ]);
        }

        // Sesión ok
        $request->session()->regenerate();

        // Redirección según rol
        $role = $authUser?->role;

        if ($role === 'admin') {
            $defaultRedirect = route('admin.dashboard');
        } elseif ($role === 'provider') {
            $defaultRedirect = route('dashboard.profile.edit');
        } elseif ($role === 'client') {
            $defaultRedirect = route('profile.edit');
        } else {
            $defaultRedirect = route('dashboard');
        }

        Log::info('login.redirecting', [
            'user_id' => $authUser?->id,
            'role' => $role,
            'redirect' => $defaultRedirect,
            'session_id' => $request->session()->getId(),
        ]);

        return redirect()->intended($defaultRedirect);
    }

    /**
     * Cerrar sesión.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
