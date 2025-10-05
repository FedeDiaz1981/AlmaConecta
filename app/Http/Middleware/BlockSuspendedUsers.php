<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockSuspendedUsers
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(); // guard web por defecto

        // Si la cuenta está suspendida o marcada como eliminada => cerrar sesión y bloquear
        if ($user && in_array($user->account_status, ['suspended', 'deleted'], true)) {

            // Cerrar sesión explícitamente en el guard web
            Auth::guard('web')->logout();

            // Invalidar sesión y regenerar token CSRF
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Si quiere JSON (API), devolver 403; si no, redirigir al login con mensaje
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $user->account_status === 'suspended'
                        ? 'Cuenta suspendida.'
                        : 'Cuenta eliminada.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $user->account_status === 'suspended'
                        ? 'Tu cuenta está suspendida. Contactá al administrador.'
                        : 'Tu cuenta fue eliminada.',
                ]);
        }

        return $next($request);
    }
}
