<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\{Profile, Service, Edit};

class ProviderProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        $profile = Profile::firstOrCreate(
            ['user_id' => $user->id],
            ['display_name' => $user->name, 'slug' => Str::slug($user->name) . '-' . $user->id]
        );

        // edición pendiente (para bloquear el form)
        $pendingEdit = Edit::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        // “Especialidad”: usamos servicios
        $services = Service::orderBy('name')->get();

        return view('dashboard.profile_edit', compact('profile', 'services', 'pendingEdit'));
    }

    public function saveDraft(Request $request)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->firstOrFail();

        // si ya hay un pending, no permitimos enviar otro
        $alreadyPending = Edit::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return back()
                ->withErrors(['general' => 'Ya tenés una petición en revisión. Anulála para poder volver a editar.'])
                ->withInput();
        }

        $data = $request->validate([
            'display_name'   => 'required|string|max:255',
            'service_id'     => 'nullable|exists:services,id',
            'modality'       => 'required|in:remoto,ambas,presencial',
            'about'          => 'nullable|string|max:10000',
            'video_url'      => 'nullable|url|max:1024',
            'photo'          => 'nullable|image|max:2048',
            // geo
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
            'country'        => 'nullable|string|max:2',
            'state'          => 'nullable|string|max:100',
            'city'           => 'nullable|string|max:100',
            'address'        => 'nullable|string|max:255',
            // contacto
            'whatsapp'       => 'nullable|string|max:30',
            'contact_email'  => 'nullable|email|max:255',
            'template_key'   => 'required|in:a,b',
        ]);

        // modalidad -> flags
        $mode_remote     = $data['modality'] === 'remoto' || $data['modality'] === 'ambas';
        $mode_presential = $data['modality'] === 'presencial' || $data['modality'] === 'ambas';

        // foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profiles', 'public');
        }

        // payload para aprobar
        $payload = [
            'display_name'    => $data['display_name'],
            'service_id'      => $data['service_id'] ?? null,
            'about'           => $data['about'] ?? null,
            'video_url'       => $data['video_url'] ?? null,
            'template_key'    => $data['template_key'],
            'mode_remote'     => $mode_remote,
            'mode_presential' => $mode_presential,
            'country'         => $data['country'] ?? null,
            'state'           => $data['state'] ?? null,
            'city'            => $data['city'] ?? null,
            'address'         => $data['address'] ?? null,
            'lat'             => $data['lat'] ?? null,
            'lng'             => $data['lng'] ?? null,
            'whatsapp'        => $data['whatsapp'] ?? null,
            'contact_email'   => $data['contact_email'] ?? null,
        ];
        if ($photoPath) {
            $payload['photo_path'] = $photoPath;
        }

        Edit::create([
            'profile_id' => $profile->id,
            'payload'    => $payload,
            'status'     => 'pending',
        ]);

        return back()->with('status', 'Borrador enviado a revisión.');
    }

    public function cancelPending(Request $request)
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->firstOrFail();

        $pending = Edit::where('profile_id', $profile->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$pending) {
            return back()->with('status', 'No hay una petición pendiente.');
        }

        // limpiar foto subida si no es la actual del perfil
        $payload = is_array($pending->payload) ? $pending->payload : (json_decode($pending->payload, true) ?? []);
        if (!empty($payload['photo_path']) && $payload['photo_path'] !== $profile->photo_path) {
            try { Storage::disk('public')->delete($payload['photo_path']); } catch (\Throwable $e) {}
        }

        // Importante: respetamos el CHECK constraint (pending/approved/rejected)
        $pending->status      = 'rejected';
        $pending->reviewed_by = $user->id;
        $pending->reviewed_at = now();
        $pending->reason      = 'Cancelado por el usuario';
        $pending->save();

        return back()->with('status', 'Petición anulada. Ya podés editar tu perfil.');
    }
}
