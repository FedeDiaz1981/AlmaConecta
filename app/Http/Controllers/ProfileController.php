<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Profile;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Perfil profesional del usuario (si no existe, no lo creamos acá)
        $profile = Profile::where('user_id', $user->id)->first();

        // Especialidades disponibles (solo activas)
        $specialties = Specialty::where('active', true)
            ->orderBy('name')
            ->get();

        return view('profile.edit', [
            'user'        => $user,
            'profile'     => $profile,
            'specialties' => $specialties,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 1) Actualizar datos de cuenta (name/email) como antes
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // 2) Manejar especialidades del perfil profesional (si existe)
        $specialtiesIds = $request->input('specialties', []);

        if (!empty($specialtiesIds)) {
            if (!is_array($specialtiesIds)) {
                return Redirect::back()
                    ->withErrors(['specialties' => 'Formato inválido de especialidades.'])
                    ->withInput();
            }

            // Filtramos solo IDs válidos existentes
            $validIds = Specialty::whereIn('id', $specialtiesIds)
                ->pluck('id')
                ->all();

            if (count($validIds) !== count($specialtiesIds)) {
                return Redirect::back()
                    ->withErrors(['specialties' => 'Alguna de las especialidades seleccionadas no es válida.'])
                    ->withInput();
            }

            // Buscamos el perfil profesional y sincronizamos
            $profile = Profile::where('user_id', $user->id)->first();

            if ($profile) {
                $profile->specialties()->sync($validIds);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
