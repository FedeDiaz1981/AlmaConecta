<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\GenericNotification;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (!$request->filled('account_type')) {
            $request->merge([
                'account_type' => 'provider',
            ]);
        }

        $request->validate([
            'account_type' => ['required', 'in:provider,client'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'document_type' => ['nullable', 'string', 'max:30', 'required_if:account_type,client'],
            'document_number' => ['nullable', 'string', 'max:50', 'required_if:account_type,client'],
            'phone' => ['nullable', 'string', 'max:30', 'required_if:account_type,client'],
        ]);

        $accountType = $request->string('account_type')->toString();
        $role = $accountType === 'client' ? 'client' : 'provider';
        $accountStatus = $role === 'client' ? 'active' : 'pending';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'account_status' => $accountStatus,
            'document_type' => $request->input('document_type'),
            'document_number' => $request->input('document_number'),
            'phone' => $request->input('phone'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($role === 'provider') {
            $this->notifyAdminNewProvider($user);
        }

        return redirect(route('dashboard', absolute: false));
    }

    private function notifyAdminNewProvider(User $user): void
    {
        $adminEmail = config('services.notifications.admin_email');

        if (! $adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->send(new GenericNotification(
                'Nueva cuenta profesional pendiente',
                [
                    'Se registró una nueva cuenta profesional pendiente de aprobación.',
                    'Nombre: '.$user->name,
                    'Email: '.$user->email,
                    'Panel de aprobaciones: '.route('admin.approvals.index'),
                ]
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
