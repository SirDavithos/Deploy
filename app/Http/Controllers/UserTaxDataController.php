<?php

namespace App\Http\Controllers;

use App\Models\UserTaxData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTaxDataController extends Controller
{
    /**
     * Guardar un nuevo registro fiscal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nit_or_ci'      => 'required|string|max:50',
            'business_name'  => 'required|string|max:255',
            'tax_regimen'    => 'nullable|string|max:100',
            'alias'          => 'nullable|string|max:255',
            'is_default'     => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        // Si es predeterminado, quitar el default a los otros
        if ($request->boolean('is_default')) {
            Auth::user()->taxData()->update(['is_default' => false]);
        }

        Auth::user()->taxData()->create($validated);

        return back()->with('status', 'Dato fiscal agregado correctamente.');
    }

    /**
     * Eliminar un registro fiscal.
     */
    public function destroy(UserTaxData $taxData)
    {
        if ($taxData->user_id !== Auth::id()) {
            abort(403);
        }

        $taxData->delete();

        return back()->with('status', 'Dato fiscal eliminado.');
    }
}