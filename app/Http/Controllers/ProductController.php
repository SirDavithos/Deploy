<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    // Mercado público (compradores)
    public function index(Request $request)
    {
        $query = Product::with(['shop', 'category', 'artisan'])
            ->withCount('reviews')
            ->where('status', 'published');

        // Filtro por búsqueda textual
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // Filtro por rango de precio
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        // Ordenación
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
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->appends($request->except('page'));

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'filters'    => $request->only(['search', 'category', 'sort', 'price_min', 'price_max']),
            'categories' => Category::all(),
        ]);
    }

    // Detalle de producto
    public function show(Product $product)
    {
        $product->load('shop', 'category', 'artisan', 'reviews.user');
        $product->setAttribute('average_rating', $product->reviews->avg('rating') ?: 0);
        $product->setAttribute('reviews_count', $product->reviews->count());

        // Productos similares (misma categoría, excluyendo el actual)
        $relatedProducts = Product::with('shop')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->take(4)
            ->get();

        $user = Auth::user();
        $product->setAttribute('is_favorited', $user ? $user->favoriteProducts()->where('product_id', $product->id)->exists() : false);
        $canReview = false;
        if ($user) {
            // Verificar si el usuario tiene un pedido entregado que contenga este producto
            $canReview = $user->orders()
                ->where('status', 'delivered')
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })->exists();
        }

        return Inertia::render('Products/Show', [
            'product'         => $product,
            'relatedProducts' => $relatedProducts ?? [],
            'canReview'       => $canReview,
        ]);
        
    }

    // Formulario para crear producto (solo artesanos con tienda)
    public function create()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            abort(403, 'Necesitas una tienda para publicar productos.');
        }

        $categories = Category::all();
        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'shop'       => $shop,
        ]);
    }

    // Guardar nuevo producto
    public function store(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            abort(403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:1',
            'category_id' => 'nullable|exists:categories,id',
            'images'      => 'nullable|array',
            'images.*'    => 'image|max:2048',
        ]);

        // Subir imágenes
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }

        $product = $shop->products()->create([
            'user_id'     => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'images'      => $imagePaths,
            'status'      => 'published',
        ]);

        return redirect()->route('products.show', $product->id)
            ->with('status', 'Producto publicado exitosamente.');
    }

    // Editar producto (solo el dueño)
    public function edit(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Products/Edit', [
            'product'    => $product->load('category'),
            'categories' => Category::all(),
        ]);
    }

    // Actualizar producto
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:1',
            'category_id'   => 'nullable|exists:categories,id',
            'status'        => 'required|in:published,draft,sold_out',
            'images'        => 'nullable|array',
            'images.*'      => 'image|max:2048',
            'delete_images' => 'nullable|array',
        ]);

        $currentImages = $product->images ?? [];

        // Eliminar imágenes marcadas
        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images') as $index) {
                if (isset($currentImages[$index])) {
                    Storage::disk('public')->delete($currentImages[$index]);
                    unset($currentImages[$index]);
                }
            }
            $currentImages = array_values($currentImages);
        }

        // Añadir nuevas imágenes
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $currentImages[] = $image->store('products', 'public');
            }
        }

        $product->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'category_id' => $validated['category_id'] ?? null,
            'status'      => $validated['status'],
            'images'      => $currentImages,
        ]);

        return redirect()->route('products.show', $product->id)
            ->with('status', 'Producto actualizado.');
    }

    // Eliminar producto
    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            abort(403);
        }

        foreach ($product->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $product->delete();

        return redirect()->route('shop.show', $product->shop_id)
            ->with('status', 'Producto eliminado.');
    }
    private function userCanReview(Product $product): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Verificar si el usuario ha comprado este producto y el pedido está entregado
        return $user->orders()
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->whereIn('status', ['delivered'])
            ->exists();
    }
}