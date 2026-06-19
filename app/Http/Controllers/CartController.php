<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
class CartController extends Controller
{
    public function index()
    {
        $items = Auth::user()->cartItems()->with('product.shop')->get();
        return Inertia::render('Cart/Index', ['cartItems' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        // Si el producto ya está en el carrito, suma cantidad
        $existing = $user->cartItems()->where('product_id', $request->product_id)->first();
        if ($existing) {
            $existing->increment('quantity', $request->quantity);
        } else {
            $user->cartItems()->create($request->only('product_id', 'quantity'));
        }

        return back()->with('status', 'Producto añadido al carrito.');
    }

    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) abort(403);

        $request->validate(['quantity' => 'required|integer|min:1']);
        $cart->update(['quantity' => $request->quantity]);

        return back()->with('status', 'Carrito actualizado.');
    }

    public function destroy(Cart $cart)
    {
        if ($cart->user_id !== Auth::id()) abort(403);
        $cart->delete();

        return back()->with('status', 'Producto eliminado del carrito.');
    }
}