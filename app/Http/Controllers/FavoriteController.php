<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    // Marcar/desmarcar tienda como favorita
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

    // Marcar/desmarcar producto como favorito
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

    // Mostrar página de favoritos
    public function index(Request $request)
    {
        $user = Auth::user();

        // Tiendas favoritas
        $favoriteShops = $user->favoriteShops()->with('owner')->get();

        // Tiendas seguidas
        $followedShops = $user->followedShops()->with('owner')->get();

        // Productos favoritos con filtros
        $query = $user->favoriteProducts()->with('shop', 'category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'alpha_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'alpha_desc':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->latest();
        }

        $favoriteProducts = $query->get();

        return Inertia::render('Favorites/Index', [
            'favoriteShops'    => $favoriteShops,
            'favoriteProducts' => $favoriteProducts,
            'followedShops'    => $followedShops,
            'filters'          => $request->only(['search', 'sort']),
        ]);
    }
}