<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Edit;

class DashboardController extends Controller
{
    public function index()
    {
        // Usuarios activos / suspendidos para la tabla
        $users = User::query()
            ->whereIn('account_status', ['active', 'suspended'])
            ->select('id', 'name', 'email', 'role', 'account_status', 'created_at')
            ->orderByDesc('id')
            ->get();

        // Pendientes de aprobación:
        // - cuentas nuevas (account_status = pending)
        // - cambios de perfil (edits = pending)
        $pendingAccounts     = User::where('account_status', 'pending')->count();
        $pendingProfileEdits = Edit::where('status', 'pending')->count();
        $pendingCount        = $pendingAccounts + $pendingProfileEdits;

        return view('admin.dashboard', compact(
            'users',
            'pendingAccounts',
            'pendingProfileEdits',
            'pendingCount'
        ));
    }
}
