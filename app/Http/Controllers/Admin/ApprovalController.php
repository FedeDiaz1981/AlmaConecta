<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Edit;

class ApprovalController extends Controller
{
    public function index()
    {
        // Cuentas nuevas pendientes (misma lógica que usabas en users_index)
        $pendingUsers = User::query()
            ->where('role', 'provider')
            ->where('account_status', 'pending')
            ->orderBy('id')
            ->get();

        // Cambios de perfil pendientes (edits)
        $pendingEdits = Edit::query()
            ->with(['profile.user'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.approvals.index', compact('pendingUsers', 'pendingEdits'));
    }
}
