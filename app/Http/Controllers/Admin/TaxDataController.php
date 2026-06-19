<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserTaxData;
use App\Models\User;               // <-- ¡Faltaba esta importación!
use Illuminate\Http\Request;

class TaxDataController extends Controller
{
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'nit_or_ci'      => 'required|string|max:50',
            'business_name'  => 'required|string|max:255',
            'tax_regimen'    => 'nullable|string|max:100',
            'alias'          => 'nullable|string|max:255',
            'is_default'     => 'boolean',
        ]);
        if ($request->boolean('is_default')) {
            $user->taxData()->update(['is_default' => false]);
        }
        $user->taxData()->create($validated);
        return back()->with('status', 'Dato fiscal agregado.');
    }

    public function update(Request $request, UserTaxData $taxData)
    {
        $validated = $request->validate([
            'nit_or_ci'      => 'required|string|max:50',
            'business_name'  => 'required|string|max:255',
            'tax_regimen'    => 'nullable|string|max:100',
            'alias'          => 'nullable|string|max:255',
            'is_default'     => 'boolean',
        ]);
        if ($request->boolean('is_default')) {
            UserTaxData::where('user_id', $taxData->user_id)
                ->where('id', '!=', $taxData->id)
                ->update(['is_default' => false]);
        }
        $taxData->update($validated);
        return back()->with('status', 'Dato fiscal actualizado.');
    }

    public function destroy(UserTaxData $taxData)
    {
        $taxData->delete();
        return back()->with('status', 'Dato fiscal eliminado.');
    }
}