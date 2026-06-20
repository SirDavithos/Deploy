<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ShopController extends Controller
{
    /**
     * Muestra el formulario para solicitar apertura de tienda.
     */
    public function create()
    {
        if (Auth::user()->shop) {
            return redirect()->route('shop.show', Auth::user()->shop->id);
        }

        return Inertia::render('Shop/Create');
    }

    /**
     * Guarda la solicitud de tienda.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->shop) {
            return back()->with('error', 'Ya tienes una tienda registrada.');
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'avatar'         => 'nullable|image|max:2048',
            'banner'         => 'nullable|image|max:4096',
            'images'         => 'nullable|array',
            'images.*'       => 'image|max:2048',
            'phone_number'   => 'required|string|max:20',
            'phone_type'     => 'required|string|max:50',
            'city'           => 'required|string|max:255',
            'zone'           => 'required|string|max:255',
            'street_address' => 'required|string|max:255',
            'reference'      => 'nullable|string|max:255',
        ]);

        // Subir avatar
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('shops/avatars', 'public');
        } else {
            $validated['avatar'] = null;
        }

        // Subir banner
        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('shops/banners', 'public');
        } else {
            $validated['banner'] = null;
        }

        // Procesar imágenes generales
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('shops', 'public');
            }
        }

        // Generar slug único
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Shop::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Crear la tienda
        $shop = $user->shop()->create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? null,
            'avatar'      => $validated['avatar'],
            'banner'      => $validated['banner'],
            'images'      => $imagePaths,
            'status'      => 'pending',
        ]);

        // Crear teléfono asociado
        $shop->phones()->create([
            'phone_number' => $validated['phone_number'],
            'type'         => $validated['phone_type'],
        ]);

        // Crear dirección asociada
        $shop->addresses()->create([
            'city'           => $validated['city'],
            'zone'           => $validated['zone'],
            'street_address' => $validated['street_address'],
            'reference'      => $validated['reference'] ?? null,
            'is_default'     => true,
        ]);

        return redirect()->route('shop.show', $shop->id)
            ->with('status', '¡Solicitud enviada! Espera la aprobación.');
    }

    /**
     * Muestra el panel de la tienda (dashboard del artesano).
     */
    public function show(Shop $shop)
    {
        if ($shop->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $shop->load('phones', 'addresses', 'owner', 'products', 'orders.items.product', 'orders.buyer', 'orders.taxData');

        return Inertia::render('Shop/Show', [
            'shop'    => $shop,
            'isAdmin' => Auth::user()->hasRole('admin'),
        ]);
    }

    /**
     * Actualiza los datos de la tienda (nombre, descripción, avatar, banner).
     */
    public function update(Request $request, Shop $shop)
    {
        if ($shop->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'avatar'      => 'nullable|image|max:2048',
            'banner'      => 'nullable|image|max:4096',
        ]);

        // Avatar
        if ($request->hasFile('avatar')) {
            if ($shop->avatar) {
                Storage::disk('public')->delete($shop->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('shops/avatars', 'public');
        } else {
            unset($validated['avatar']);
        }

        // Banner
        if ($request->hasFile('banner')) {
            if ($shop->banner) {
                Storage::disk('public')->delete($shop->banner);
            }
            $validated['banner'] = $request->file('banner')->store('shops/banners', 'public');
        } else {
            unset($validated['banner']);
        }

        $shop->update($validated);

        return back()->with('status', 'Tienda actualizada correctamente.');
    }

    /**
     * Vista pública de la tienda (compradores).
     */
    public function view(Shop $shop)
    {
        $shop->load([
            'owner',
            'phones',
            'products' => function ($query) {
                $query->where('status', 'published')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->latest();
            },
            'products.reviews' => function ($query) {
                $query->latest()->with('user');
            },
            'orders' => function ($query) {
                $query->where('status', 'delivered');
            },
        ]);

        // Calcular promedio y conteo general de la tienda
        $allReviews = $shop->products->flatMap->reviews;
        $shop->setAttribute('products_avg_rating', $allReviews->avg('rating') ?? 0);
        $shop->setAttribute('products_reviews_count', $allReviews->count());
        $shop->setAttribute('sales_count', $shop->orders->count());
        $shop->setAttribute('all_reviews', $allReviews);

        // Determinar si el usuario actual sigue esta tienda
        $user = Auth::user();
        $shop->setAttribute('is_followed_by_user', $user ? $user->followedShops()->where('shop_id', $shop->id)->exists() : false);

        return Inertia::render('Shop/View', [
            'shop' => $shop,
        ]);
    }

    /**
     * Seguir / dejar de seguir una tienda.
     */
    public function toggleFollow(Request $request, Shop $shop)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $isFollowing = $user->followedShops()->where('shop_id', $shop->id)->exists();

        if ($isFollowing) {
            $user->followedShops()->detach($shop->id);
            $newState = false;
        } else {
            $user->followedShops()->attach($shop->id);
            $newState = true;
        }

        return back()->with('status', $newState ? 'Siguiendo tienda' : 'Dejaste de seguir');
    }

    /**
     * Listado de tiendas para administración.
     */
    public function index()
    {
        abort(404);
    }
}