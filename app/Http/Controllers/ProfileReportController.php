<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileReportController extends Controller
{
    public function store(Request $request, Profile $profile): RedirectResponse
    {
        $user = $request->user();

        if (!$user || ($user->role ?? null) !== 'client' || ($user->account_status ?? 'active') !== 'active') {
            abort(403);
        }

        if ($profile->is_suspended || !in_array($profile->status, ['approved', 'active'], true)) {
            abort(404);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        ProfileReport::updateOrCreate(
            ['profile_id' => $profile->id, 'user_id' => $user->id],
            [
                'reason' => trim($data['reason']),
                'status' => 'pending',
                'action' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        return back()->with('status', 'Gracias, tu reporte fue enviado.');
    }
}
