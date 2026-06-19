<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('phones', 'addresses', 'taxData', 'shop', 'roles');

        $orders = $user->orders()
            ->with(['items.product', 'shop', 'taxData'])
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [
            'phones'    => $user->phones,
            'addresses' => $user->addresses,
            'taxData'   => $user->taxData,
            'orders'    => $orders,
            'roles'     => $user->roles->pluck('slug'),   // ['admin', 'artisan', ...]
            'userShop'  => $user->shop,                   // la tienda si existe, null si no
        ]);
    }
}