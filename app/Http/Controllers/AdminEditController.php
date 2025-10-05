<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Edit;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericNotification;
use Illuminate\Support\Facades\Storage;

class AdminEditController extends Controller
{
    // SIN middleware acá; lo aplica el grupo de rutas /admin en web.php
    public function __construct()
    {
        // vacío a propósito
    }

    public function index()
    {
        $edits = Edit::with('profile')->where('status', 'pending')->latest()->paginate(20);
        return view('admin.edits_index', compact('edits'));
    }

    public function approve(Edit $edit)
    {
        DB::transaction(function () use ($edit) {
            $p = $edit->profile()->lockForUpdate()->firstOrFail();

            $payload = is_array($edit->payload)
                ? $edit->payload
                : (json_decode($edit->payload, true) ?? []);

            // Aplicar campos del payload
            $p->fill([
                'display_name' => $payload['display_name'] ?? $p->display_name,
                'service_id' => $payload['service_id'] ?? $p->service_id,
                'about' => $payload['about'] ?? $p->about,
                'video_url' => $payload['video_url'] ?? $p->video_url,
                'template_key' => $payload['template_key'] ?? $p->template_key,
                'mode_remote' => (bool) ($payload['mode_remote'] ?? $p->mode_remote),
                'mode_presential' => (bool) ($payload['mode_presential'] ?? $p->mode_presential),
                'country' => $payload['country'] ?? $p->country,
                'state' => $payload['state'] ?? $p->state,
                'city' => $payload['city'] ?? $p->city,
                'address' => $payload['address'] ?? $p->address,
                'lat' => $payload['lat'] ?? $p->lat,
                'lng' => $payload['lng'] ?? $p->lng,
                'whatsapp' => $payload['whatsapp'] ?? $p->whatsapp,
                'contact_email' => $payload['contact_email'] ?? $p->contact_email,
            ]);

            // Aplicar foto si vino en el payload
            if (!empty($payload['photo_path'])) {
                // si hay una foto anterior diferente, la borro
                if ($p->photo_path && $p->photo_path !== $payload['photo_path']) {
                    try {
                        Storage::disk('public')->delete($p->photo_path);
                    } catch (\Throwable $e) {/* noop */
                    }
                }
                $p->photo_path = $payload['photo_path'];
            }

            // Forzar estado aprobado (aunque no estuviera en $fillable)
            $p->status = 'approved';
            $p->save();

            // Media (si viene en payload)
            if (isset($payload['media'])) {
                $p->media()->delete();
                foreach ((array) $payload['media'] as $m) {
                    if (!empty($m['url'])) {
                        $p->media()->create([
                            'type' => $m['type'] ?? 'image',
                            'url' => $m['url'],
                            'position' => $m['position'] ?? 0,
                        ]);
                    }
                }
            }

            // Marcar el edit como aprobado
            $edit->status = 'approved';
            $edit->reviewed_by = auth()->id();
            $edit->reviewed_at = now();
            $edit->save();
        });

        return back()->with('status', 'Perfil aprobado.');
    }

    public function reject(Edit $edit, Request $request)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);

        $edit->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'reason' => $request->input('reason'),
        ]);

        if ($edit->profile && $edit->profile->user) {
            Mail::to($edit->profile->user->email)->send(new GenericNotification(
                'Perfil/cambio rechazado',
                [
                    '¡Hola ' . $edit->profile->user->name . '!',
                    'Tu solicitud fue rechazada.',
                    $request->input('reason') ? 'Motivo: ' . $request->input('reason') : '',
                ]
            ));
        }

        return back()->with('status', 'Cambios rechazados.');
    }
}
