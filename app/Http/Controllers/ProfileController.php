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

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name'          => 'required|string|max:255',
            'paternal_last_name'  => 'required|string|max:255',
            'maternal_last_name'  => 'nullable|string|max:255',
            'ci_number'           => 'required|string|max:20|unique:users,ci_number,'.$user->id,
            'ci_extension'        => 'required|string|size:2',
            'birth_date'          => 'required|date|before:today',
            'email'               => 'required|email|unique:users,email,'.$user->id,
            'avatar'              => 'nullable|image|max:2048',
        ]);

        // Procesar avatar
        if ($request->hasFile('avatar')) {
            // Eliminar avatar anterior si existe
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        // Si el email cambió, resetear verificación
        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        return back()->with('status', 'Perfil actualizado correctamente.');
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