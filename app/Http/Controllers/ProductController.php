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
            ->where('status', 'published');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        $products = $query->latest()->paginate(12)->appends($request->except('page'));

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'filters'    => $request->only(['search', 'category']),
            'categories' => Category::all(),
        ]);
    }

    // Detalle de producto
    public function show(Product $product)
    {
        $product->load('shop', 'category', 'artisan');
        return Inertia::render('Products/Show', ['product' => $product]);
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
            'delete_images' => 'nullable|array', // índices de imágenes a eliminar
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
}