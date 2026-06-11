<?php

namespace App\Http\Controllers;

use App\Mail\GenericNotification;
use App\Models\Profile;
use App\Models\ProfileReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $report = ProfileReport::updateOrCreate(
            ['profile_id' => $profile->id, 'user_id' => $user->id],
            [
                'reason' => trim($data['reason']),
                'status' => 'pending',
                'action' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        $this->notifyAdminProfileReport($report);

        return back()->with('status', 'Gracias, tu reporte fue enviado.');
    }

    private function notifyAdminProfileReport(ProfileReport $report): void
    {
        $adminEmail = config('services.notifications.admin_email');

        if (! $adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->send(new GenericNotification(
                'Nuevo reporte de perfil',
                [
                    'Un usuario reportó un perfil y requiere revisión.',
                    'Perfil: '.($report->profile?->display_name ?? 'Perfil no disponible'),
                    'Reportado por: '.($report->user?->email ?? 'Usuario no disponible'),
                    'Motivo: '.Str::limit($report->reason, 500),
                    'Panel de reportes: '.route('admin.reports.index'),
                ]
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
