<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->whereIn('account_status', ['active', 'suspended'])
            ->select('id', 'name', 'email', 'role', 'account_status', 'created_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.dashboard', compact('users'));
    }
}
