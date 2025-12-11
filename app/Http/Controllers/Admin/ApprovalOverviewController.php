<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Edit;

class ApprovalOverviewController extends Controller
{
    public function index()
    {
        // 1) Cuentas nuevas (usuarios con account_status = 'pending')
        $pendingUsers = User::query()
            ->with('profile')
            ->where('account_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        // 2) Cambios de perfiles existentes (tabla edits, status = 'pending')
        $pendingEdits = Edit::query()
            ->with(['profile.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        // 3) Conteo total (para mostrar en dashboard u otros lados)
        $pendingCount = $pendingUsers->count() + $pendingEdits->count();

        // 4) Si querés una lista unificada ordenada cronológicamente
        $items = collect()
            ->merge(
                $pendingUsers->map(fn ($u) => [
                    'type'       => 'user',
                    'created_at' => $u->created_at,
                    'model'      => $u,
                ])
            )
            ->merge(
                $pendingEdits->map(fn ($e) => [
                    'type'       => 'edit',
                    'created_at' => $e->created_at,
                    'model'      => $e,
                ])
            )
            ->sortBy('created_at');

        return view('admin.approvals.index', [
            'pendingUsers' => $pendingUsers,
            'pendingEdits' => $pendingEdits,
            'pendingCount' => $pendingCount,
            'items'        => $items,
        ]);
    }
}
