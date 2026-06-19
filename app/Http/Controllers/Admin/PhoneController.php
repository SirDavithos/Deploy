<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPhone;
use App\Models\User;               // <-- añadir esta línea
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'type'         => 'required|string|max:50',
        ]);
        $user->phones()->create($validated);
        return back()->with('status', 'Teléfono agregado.');
    }

    public function update(Request $request, UserPhone $phone)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'type'         => 'required|string|max:50',
        ]);
        $phone->update($validated);
        return back()->with('status', 'Teléfono actualizado.');
    }

    public function destroy(UserPhone $phone)
    {
        $phone->delete();
        return back()->with('status', 'Teléfono eliminado.');
    }
}