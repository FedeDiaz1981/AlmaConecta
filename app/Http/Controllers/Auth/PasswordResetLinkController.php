<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validateWithBag('passwordReset', [
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Ingresá tu correo electrónico.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
        ]);

        $user = User::query()
            ->where('email', $request->input('email'))
            ->where('account_status', 'active')
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Ese mail no pertenece a una cuenta activa.',
            ], 'passwordReset');
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('passwordResetStatus', 'Te enviamos un mail con el link para restablecer tu contraseña.');
        }

        $friendly = [
            Password::INVALID_USER => 'No encontramos una cuenta activa con ese correo.',
            Password::RESET_THROTTLED => 'Esperá un momento antes de volver a solicitar el link.',
        ];

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $friendly[$status] ?? 'No pudimos enviar el correo. Intentá de nuevo.'], 'passwordReset');
    }
}
