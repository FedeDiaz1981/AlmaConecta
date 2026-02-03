<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileReportController extends Controller
{
    public function index()
    {
        $reports = ProfileReport::with(['profile.user', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.reports_index', compact('reports'));
    }

    public function dismiss(ProfileReport $report): RedirectResponse
    {
        $report->update([
            'status' => 'dismissed',
            'action' => null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Reporte descartado.');
    }

    public function suspend(ProfileReport $report): RedirectResponse
    {
        DB::transaction(function () use ($report) {
            $profile = $report->profile;
            if ($profile) {
                $profile->is_suspended = true;
                $profile->suspended_at = now();
                $profile->save();
            }

            $report->update([
                'status' => 'actioned',
                'action' => 'suspended',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('status', 'Perfil suspendido.');
    }

    public function deleteProfile(ProfileReport $report): RedirectResponse
    {
        DB::transaction(function () use ($report) {
            $profile = $report->profile;

            $report->update([
                'status' => 'actioned',
                'action' => 'deleted',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            if ($profile) {
                $profile->delete();
            }
        });

        return back()->with('status', 'Perfil eliminado.');
    }
}
