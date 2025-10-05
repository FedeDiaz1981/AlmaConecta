<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Pre-chequeo: si la cuenta está suspendida/eliminada, no intentamos autenticar
        $user = User::where('email', $request->input('email'))->first();
        if ($user && in_array($user->account_status, ['suspended', 'deleted'], true)) {
            throw ValidationException::withMessages([
                'email' => $user->account_status === 'suspended'
                    ? 'Tu cuenta está suspendida. Contactá al administrador.'
                    : 'Tu cuenta fue eliminada.',
            ]);
        }

        // Autenticar credenciales (Breeze)
        $request->authenticate();

        // Post-chequeo defensivo: si entre el intento y ahora se suspendió/eliminó
        $authUser = Auth::user();
        if ($authUser && in_array($authUser->account_status, ['suspended', 'deleted'], true)) {
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

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Cerrar sesión.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
