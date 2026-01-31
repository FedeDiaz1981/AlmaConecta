<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Edit;
use App\Mail\GenericNotification;

class AdminEditController extends Controller
{
    // SIN middleware acá; lo aplica el grupo de rutas /admin en web.php
    public function __construct()
    {
        // vacío a propósito
    }

    public function index()
    {
        $edits = Edit::with('profile')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.edits_index', compact('edits'));
    }

    public function approve(Edit $edit)
    {
        DB::transaction(function () use ($edit) {
            /** @var \App\Models\Profile $profile */
            $profile = $edit->profile()->lockForUpdate()->firstOrFail();

            $payload = is_array($edit->payload)
                ? $edit->payload
                : (json_decode($edit->payload, true) ?? []);

            // Helper: setear solo si el payload trae la key (aunque sea null/false)
            $setIfExists = function (array &$updates, string $key) use ($payload) {
                if (array_key_exists($key, $payload)) {
                    $updates[$key] = $payload[$key];
                }
            };

            $updates = [];

            /*
             |----------------------------------------------------------------------
             | Datos principales
             |----------------------------------------------------------------------
             */
            $setIfExists($updates, 'display_name');
            $setIfExists($updates, 'service_id');     // si tu tabla no tiene este campo, no pasa nada si no está fillable
            $setIfExists($updates, 'about');
            $setIfExists($updates, 'video_url');
            $setIfExists($updates, 'template_key');

            // booleans: respetar false
            if (array_key_exists('mode_remote', $payload)) {
                $updates['mode_remote'] = (bool) $payload['mode_remote'];
            }
            if (array_key_exists('mode_presential', $payload)) {
                $updates['mode_presential'] = (bool) $payload['mode_presential'];
            }

            /*
             |----------------------------------------------------------------------
             | Ubicación GeoRef (fuente de verdad)
             |----------------------------------------------------------------------
             */
            $setIfExists($updates, 'province_id');
            $setIfExists($updates, 'province_name');
            $setIfExists($updates, 'city_id');
            $setIfExists($updates, 'city_name');

            $setIfExists($updates, 'address');
            $setIfExists($updates, 'address_extra');

            $setIfExists($updates, 'lat');
            $setIfExists($updates, 'lng');

            /*
             |----------------------------------------------------------------------
             | Compatibilidad con vistas actuales (state/city/country)
             | - Solo setear si viene data nueva (para no pisar)
             |----------------------------------------------------------------------
             */
            // country: si el payload trae country, lo usamos; sino mantenemos (o AR si querés forzar)
            if (array_key_exists('country', $payload)) {
                $updates['country'] = $payload['country'] ?: 'AR';
            } else {
                // si no existe en payload, mantenemos lo que tenga
                // (si querés forzar siempre AR, descomentá la línea de abajo)
                // $updates['country'] = 'AR';
            }

            // state/city: derivamos de province_name/city_name solo si esas keys vienen
            if (array_key_exists('province_name', $payload)) {
                $updates['state'] = $payload['province_name'];
            }
            if (array_key_exists('city_name', $payload)) {
                $updates['city'] = $payload['city_name'];
            }

            /*
             |----------------------------------------------------------------------
             | Contacto
             |----------------------------------------------------------------------
             */
            $setIfExists($updates, 'whatsapp');
            $setIfExists($updates, 'contact_email');

            // Aplicar updates (solo lo que vino en payload)
            if (!empty($updates)) {
                $profile->fill($updates);
            }

            /*
             |----------------------------------------------------------------------
             | Si NO es presencial, limpiamos ubicación/dirección/coords
             | (evita que queden valores viejos “pegados”)
             |----------------------------------------------------------------------
             */
            $isPresential = (bool) $profile->mode_presential;

            if (!$isPresential) {
                $profile->province_id = null;
                $profile->province_name = null;
                $profile->city_id = null;
                $profile->city_name = null;
                $profile->address = null;
                $profile->address_extra = null;
                $profile->lat = null;
                $profile->lng = null;

                // compat
                $profile->state = null;
                $profile->city = null;
                $profile->country = $profile->country ?: 'AR';
            } else {
                // si es presencial, aseguramos country (por prolijidad)
                $profile->country = $profile->country ?: 'AR';
            }

            /*
             |----------------------------------------------------------------------
             | Foto
             |----------------------------------------------------------------------
             */
            if (array_key_exists('photo_path', $payload) && !empty($payload['photo_path'])) {
                $newPath = $payload['photo_path'];

                if ($profile->photo_path && $profile->photo_path !== $newPath) {
                    try {
                        Storage::disk('public')->delete($profile->photo_path);
                    } catch (\Throwable $e) {
                        // noop
                    }
                }
                $profile->photo_path = $newPath;
            }

            /*
             |----------------------------------------------------------------------
             | Estado aprobado
             |----------------------------------------------------------------------
             */
            $profile->status = 'approved';
            $profile->approved_at = now();
            $profile->save();

            /*
             |----------------------------------------------------------------------
             | Especialidades
             | - Si viene la key, sincronizamos (aunque sea array vacío)
             |----------------------------------------------------------------------
             */
            if (array_key_exists('specialties', $payload)) {
                $ids = is_array($payload['specialties'])
                    ? array_values(array_unique(array_map('intval', $payload['specialties'])))
                    : [];

                $profile->specialties()->sync($ids);
            }

            /*
             |----------------------------------------------------------------------
             | Media (si existiera)
             |----------------------------------------------------------------------
             */
            if (array_key_exists('media', $payload)) {
                $profile->media()->delete();

                foreach ((array) $payload['media'] as $m) {
                    if (!empty($m['url'])) {
                        $profile->media()->create([
                            'type'     => $m['type'] ?? 'image',
                            'url'      => $m['url'],
                            'position' => $m['position'] ?? 0,
                        ]);
                    }
                }
            }

            /*
             |----------------------------------------------------------------------
             | Marcar edición como aprobada
             |----------------------------------------------------------------------
             */
            $edit->status      = 'approved';
            $edit->reviewed_by = Auth::id();
            $edit->reviewed_at = now();
            $edit->save();
        });

        return back()->with('status', 'Perfil aprobado.');
    }

    public function reject(Edit $edit, Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000'
        ]);

        // Cleanup: si el edit tenía una foto nueva en payload, borrarla (si no es la del perfil actual)
        try {
            $payload = is_array($edit->payload)
                ? $edit->payload
                : (json_decode($edit->payload, true) ?? []);

            $profile = $edit->profile;

            if (!empty($payload['photo_path'])) {
                $payloadPath = (string) $payload['photo_path'];
                $currentPath = $profile ? (string) ($profile->photo_path ?? '') : '';

                if ($payloadPath !== '' && $payloadPath !== $currentPath) {
                    Storage::disk('public')->delete($payloadPath);
                }
            }
        } catch (\Throwable $e) {
            // silencioso
        }

        $edit->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'reason'      => $request->input('reason'),
        ]);

        if ($edit->profile && $edit->profile->user) {
            Mail::to($edit->profile->user->email)->send(
                new GenericNotification(
                    'Perfil / cambios rechazados',
                    [
                        'Hola ' . $edit->profile->user->name . ',',
                        'Tu solicitud de cambios fue rechazada.',
                        $request->input('reason') ? 'Motivo: ' . $request->input('reason') : '',
                    ]
                )
            );
        }

        return back()->with('status', 'Cambios rechazados.');
    }
}
