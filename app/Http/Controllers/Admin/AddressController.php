<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Models\User;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Agregar una dirección a un usuario específico (admin).
     */
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'city'           => 'required|string|max:255',
            'zone'           => 'required|string|max:255',
            'street_address' => 'required|string|max:255',
            'reference'      => 'nullable|string|max:255',
            'is_default'     => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated);

        return back()->with('status', 'Dirección agregada correctamente.');
    }

    /**
     * Actualizar una dirección existente (admin).
     */
    public function update(Request $request, UserAddress $address)
    {
        $validated = $request->validate([
            'city'           => 'required|string|max:255',
            'zone'           => 'required|string|max:255',
            'street_address' => 'required|string|max:255',
            'reference'      => 'nullable|string|max:255',
            'is_default'     => 'boolean',
        ]);

        if ($request->boolean('is_default')) {
            UserAddress::where('user_id', $address->user_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('status', 'Dirección actualizada.');
    }

    /**
     * Eliminar una dirección (admin).
     */
    public function destroy(UserAddress $address)
    {
        $address->delete();

        return back()->with('status', 'Dirección eliminada.');
    }
}