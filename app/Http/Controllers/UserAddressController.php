<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    /**
     * Guardar una nueva dirección para el usuario autenticado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'city'           => 'required|string|max:255',
            'zone'           => 'required|string|max:255',
            'street_address' => 'required|string|max:255',
            'reference'      => 'nullable|string|max:255',
            'is_default'     => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        // Si marca como predeterminada, quitar esa marca a las demás
        if ($request->boolean('is_default')) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        Auth::user()->addresses()->create($validated);

        return back()->with('status', 'Dirección agregada correctamente.');
    }

    /**
     * Eliminar una dirección del usuario autenticado.
     */
    public function destroy(UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para eliminar esta dirección.');
        }

        $address->delete();

        return back()->with('status', 'Dirección eliminada.');
    }
    public function update(Request $request, UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'city'           => 'required|string|max:255',
            'zone'           => 'required|string|max:255',
            'street_address' => 'required|string|max:255',
            'reference'      => 'nullable|string|max:255',
            'is_default'     => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('status', 'Dirección actualizada.');
    }
}