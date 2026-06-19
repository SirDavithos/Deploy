<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf; // al inicio



class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Shop::with('owner');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('paternal_last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shops = $query->latest()->paginate(20)->appends($request->except('page'));

        return Inertia::render('Admin/Shops/Index', [
            'shops' => $shops,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(Shop $shop)
    {
        $shop->load('owner', 'phones', 'addresses', 'products', 'orders');
        return Inertia::render('Admin/Shops/Show', ['shop' => $shop]);
    }

    public function approve(Shop $shop)
    {
        $shop->update(['status' => 'approved']);
        return back()->with('status', 'Tienda aprobada.');
    }

    public function reject(Shop $shop)
    {
        $shop->update(['status' => 'rejected']);
        return back()->with('status', 'Tienda rechazada.');
    }



    public function exportPdf(Request $request)
    {
        $query = Shop::with('owner');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhereHas('owner', function($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('paternal_last_name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shops = $query->latest()->get();

        $pdf = Pdf::loadView('pdf.admin.shops', compact('shops'));
        return $pdf->download('tiendas.pdf');
    }
}