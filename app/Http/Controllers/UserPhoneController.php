<?php

namespace App\Http\Controllers;

use App\Models\UserPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPhoneController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'type'         => 'required|string|max:50',
        ]);

        Auth::user()->phones()->create($request->only('phone_number', 'type'));

        return back()->with('status', 'Teléfono agregado.');
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
        ]);

        $phone->update($validated);

        return back()->with('status', 'Teléfono actualizado.');
    }
}