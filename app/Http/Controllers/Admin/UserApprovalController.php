<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Edit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class UserApprovalController extends Controller
{
    /**
     * Las rutas /admin ya aplican auth + can:admin en web.php, por lo que
     * no es estrictamente necesario volver a declararlo acá.
     */
    public function __construct()
    {
        // vacío a propósito
    }

    /**
     * Listado de cuentas (la vista puede decidir qué bloques renderizar).
     */
    public function index()
    {
        $pending   = User::where('role', 'provider')
            ->where('account_status', 'pending')
            ->latest()
            ->paginate(20);

        // si tu vista ya no muestra suspendidos, no pasa nada por enviarlo
        $suspended = User::where('account_status', 'suspended')
            ->latest()
            ->paginate(20);

        return view('admin.users_index', compact('pending', 'suspended'));
    }

    /**
     * Aprobar cuenta.
     */
    public function approve(User $user)
    {
        $user->update([
            'account_status' => 'active',
            'approved_at'    => now(),
        ]);

        // Opcional: enviar e-mail
        // Mail::to($user->email)->send(new \App\Mail\GenericNotification('Cuenta aprobada', ['Tu cuenta fue aprobada.']));

        return back()->with('status', 'Cuenta aprobada.');
    }

    /**
     * Rechazar cuenta (con motivo opcional).
     */
    public function reject(User $user, Request $request)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        $user->update([
            'account_status' => 'rejected',
            'rejected_at'    => now(),
            'reject_reason'  => $request->string('reason')->toString(),
        ]);

        // Mail::to($user->email)->send(new \App\Mail\GenericNotification('Cuenta rechazada', ['Motivo: '.$request->input('reason')]));

        return back()->with('status', 'Cuenta rechazada.');
    }

    /**
     * Suspender cuenta (bloquea acceso).
     */
    public function suspend(User $user, Request $request)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        $user->update([
            'account_status' => 'suspended',
            'suspended_at'   => now(),
            'suspend_reason' => $request->string('reason')->toString(),
        ]);

        // Mail::to($user->email)->send(new \App\Mail\GenericNotification('Cuenta suspendida', ['Motivo: '.$request->input('reason')]));

        return back()->with('status', 'Cuenta suspendida.');
    }

    /**
     * Activar/rehabilitar cuenta (quita suspensión).
     */
    public function activate(User $user)
    {
        $user->update([
            'account_status' => 'active',
            'activated_at'   => now(),
            'suspend_reason' => null,
        ]);

        // Mail::to($user->email)->send(new \App\Mail\GenericNotification('Cuenta reactivada', ['Tu cuenta fue reactivada.']));

        return back()->with('status', 'Cuenta reactivada.');
    }

    /**
     * Eliminar DEFINITIVAMENTE la cuenta y sus datos asociados.
     * - Evitamos que un admin se elimine a sí mismo por accidente.
     * - Borramos perfil, foto y edits relacionados si existen.
     */
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors('No podés eliminar tu propia cuenta.');
        }

        DB::transaction(function () use ($user) {
            // Perfil del usuario (si existe)
            $profile = Profile::where('user_id', $user->id)->first();

            if ($profile) {
                // foto
                if (!empty($profile->photo_path)) {
                    try {
                        Storage::disk('public')->delete($profile->photo_path);
                    } catch (\Throwable $e) {
                        // silencioso
                    }
                }

                // edits asociados al perfil
                Edit::where('profile_id', $profile->id)->delete();

                // borrar perfil
                $profile->delete();
            }

            // finalmente, borrar el usuario
            $user->delete();
        });

        return back()->with('status', 'Cuenta eliminada correctamente.');
    }
}
