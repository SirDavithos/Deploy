<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status'          => session('status'),
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Validamos todos los campos de tu tabla users
        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:255'],
            'paternal_last_name' => ['required', 'string', 'max:255'],
            'maternal_last_name' => ['nullable', 'string', 'max:255'],
            'ci_number'          => ['required', 'string', 'max:20', 'unique:users,ci_number,' . $user->id],
            'ci_extension'       => ['required', 'string', 'size:2'],
            'birth_date'         => ['required', 'date', 'before:today'],
            'email'              => ['required', 'email', 'unique:users,email,' . $user->id],
            'avatar'             => ['nullable', 'image', 'max:2048'], // máx 2 MB
        ]);

        // Manejo de la foto de perfil (avatar)
        if ($request->hasFile('avatar')) {
            // Eliminar el avatar anterior si existía
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            // Si no se envía archivo, no tocamos el avatar actual
            unset($validated['avatar']);
        }

        // Si el correo cambió, marcamos la verificación como no realizada
        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
        }

        $user->fill($validated);
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'Perfil actualizado correctamente.');
    }

    /**
     * Elimina la cuenta del usuario.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
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