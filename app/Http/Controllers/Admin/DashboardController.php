<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    // El middleware "auth" y "can:admin" ya se aplica en las rutas (web.php).
    // No uses $this->middleware() aquí para evitar el error.

    public function index()
    {
        // Usuarios activos para la grilla del dashboard admin
        $users = User::query()
            ->whereIn('account_status', ['active', 'suspended'])
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.dashboard', compact('users'));
    }
}
