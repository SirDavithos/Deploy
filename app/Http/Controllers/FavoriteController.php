<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    // Alternar favorito de tienda
    public function toggleShop(Request $request, Shop $shop)
    {
        $user = Auth::user();
        $isFav = $user->favoriteShops()->where('shop_id', $shop->id)->exists();

        if ($isFav) {
            $user->favoriteShops()->detach($shop->id);
        } else {
            $user->favoriteShops()->attach($shop->id);
        }

        return back()->with('status', $isFav ? 'Tienda eliminada de favoritos' : 'Tienda añadida a favoritos');
    }

    // Alternar favorito de producto
    public function toggleProduct(Request $request, Product $product)
    {
        $user = Auth::user();
        $isFav = $user->favoriteProducts()->where('product_id', $product->id)->exists();

        if ($isFav) {
            $user->favoriteProducts()->detach($product->id);
        } else {
            $user->favoriteProducts()->attach($product->id);
        }

        return back()->with('status', $isFav ? 'Producto eliminado de favoritos' : 'Producto añadido a favoritos');
    }

    // Listar favoritos del usuario
    public function index()
    {
        $user = Auth::user();
        $shops = $user->favoriteShops()->with('owner')->get();
        $products = $user->favoriteProducts()->with(['shop', 'category'])->get();

        return Inertia::render('Favorites/Index', [
            'favoriteShops'    => $shops,
            'favoriteProducts' => $products,
        ]);
    }
}