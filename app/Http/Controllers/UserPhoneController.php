<?php

namespace App\Http\Controllers;

use App\Models\UserPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPhoneController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'type'         => 'required|string|max:50',
            'is_default'   => 'boolean',
        ]);

        // Si se marca como predeterminado, desmarcar los demás
        if ($request->boolean('is_default')) {
            Auth::user()->phones()->update(['is_default' => false]);
        }

        Auth::user()->phones()->create($validated);

        return back()->with('status', 'Teléfono agregado correctamente.');
    }

    public function destroy(UserPhone $phone)
    {
        // Verificar que el teléfono pertenezca al usuario autenticado
        if ($phone->user_id !== Auth::id()) {
            abort(403);
        }

        $phone->delete();

        return back()->with('status', 'Teléfono eliminado.');
    }
    public function update(Request $request, UserPhone $phone)
    {
        if ($phone->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'type'         => 'required|string|max:50',
            'is_default'   => 'boolean',
        ]);

        // Si se marca como predeterminado, desmarcar los demás
        if ($request->boolean('is_default')) {
            Auth::user()->phones()->where('id', '!=', $phone->id)->update(['is_default' => false]);
        }

        $phone->update($validated);

        return back()->with('status', 'Teléfono actualizado correctamente.');
    }
}