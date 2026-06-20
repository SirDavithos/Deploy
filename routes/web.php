<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserPhoneController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserTaxDataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\PhoneController as AdminPhoneController;
use App\Http\Controllers\Admin\AddressController as AdminAddressController;
use App\Http\Controllers\Admin\TaxDataController as AdminTaxDataController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

// Página de inicio con categorías y múltiples secciones de productos
Route::get('/', function () {
    $user = auth()->user();

    return Inertia::render('Welcome', [
        'canLogin'         => Route::has('login'),
        'canRegister'      => Route::has('register'),
        'laravelVersion'   => Application::VERSION,
        'phpVersion'       => PHP_VERSION,
        'categories'       => \App\Models\Category::withCount('products')->get(),
        // Productos destacados (los más recientes)
        'featuredProducts' => \App\Models\Product::with(['shop', 'artisan'])
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get(),
        // Más vendidos (por cantidad de pedidos)
        'bestSellers'      => \App\Models\Product::with(['shop', 'artisan'])
            ->where('status', 'published')
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(4)
            ->get(),
        // Mejor valorados (por promedio de reseñas)
        'topRated'         => \App\Models\Product::with(['shop', 'artisan'])
            ->where('status', 'published')
            ->withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(4)
            ->get(),
        // Recién llegados (los últimos 4 publicados)
        'newArrivals'      => \App\Models\Product::with(['shop', 'artisan'])
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get(),
        // Datos del usuario autenticado (si existe) – aún se pasan por si los necesita alguna parte
        'authUserRoles'    => $user ? $user->roles()->pluck('slug') : [],
        'authUserShop'     => $user ? $user->shop : null,
    ]);
})->name('home');

// Mercado público (sin necesidad de autenticación)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/tienda/{shop:slug}', [ShopController::class, 'view'])->name('shop.view');

/*
|--------------------------------------------------------------------------
| Rutas protegidas (auth + verified)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tienda del usuario autenticado
    Route::get('/shop/create', [ShopController::class, 'create'])->name('shop.create');
    Route::post('/shop', [ShopController::class, 'store'])->name('shop.store');
    Route::get('/shop/{shop}', [ShopController::class, 'show'])->name('shop.show');
    Route::match(['patch', 'post'], '/shop/{shop}', [ShopController::class, 'update'])->name('shop.update');
    // Productos (gestión)
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Teléfonos (usuario)
    Route::post('/user/phones', [UserPhoneController::class, 'store'])->name('user.phones.store');
    Route::patch('/user/phones/{phone}', [UserPhoneController::class, 'update'])->name('user.phones.update');
    Route::delete('/user/phones/{phone}', [UserPhoneController::class, 'destroy'])->name('user.phones.destroy');

    // Direcciones (usuario)
    Route::post('/user/addresses', [UserAddressController::class, 'store'])->name('user.addresses.store');
    Route::patch('/user/addresses/{address}', [UserAddressController::class, 'update'])->name('user.addresses.update');
    Route::delete('/user/addresses/{address}', [UserAddressController::class, 'destroy'])->name('user.addresses.destroy');

    // Facturación (usuario)
    Route::post('/user/tax-data', [UserTaxDataController::class, 'store'])->name('user.tax-data.store');
    Route::patch('/user/tax-data/{taxData}', [UserTaxDataController::class, 'update'])->name('user.tax-data.update');
    Route::delete('/user/tax-data/{taxData}', [UserTaxDataController::class, 'destroy'])->name('user.tax-data.destroy');

    // Carrito
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Pedidos
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
});

/*
|--------------------------------------------------------------------------
| Rutas de perfil (auth)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::post('/tienda/{shop}/follow', [ShopController::class, 'toggleFollow'])->name('shop.follow');
    
});

/*
|--------------------------------------------------------------------------
| Rutas de administración (auth + verified + admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Usuarios
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::get('/users/export-pdf', [AdminUserController::class, 'exportPdf'])->name('users.export-pdf');

    // Teléfonos de cualquier usuario (admin)
    Route::post('/users/{user}/phones', [AdminPhoneController::class, 'store'])->name('users.phones.store');
    Route::patch('/users/phones/{phone}', [AdminPhoneController::class, 'update'])->name('users.phones.update');
    Route::delete('/users/phones/{phone}', [AdminPhoneController::class, 'destroy'])->name('users.phones.destroy');

    // Direcciones de cualquier usuario (admin)
    Route::post('/users/{user}/addresses', [AdminAddressController::class, 'store'])->name('users.addresses.store');
    Route::patch('/users/addresses/{address}', [AdminAddressController::class, 'update'])->name('users.addresses.update');
    Route::delete('/users/addresses/{address}', [AdminAddressController::class, 'destroy'])->name('users.addresses.destroy');

    // NIT de cualquier usuario (admin)
    Route::post('/users/{user}/tax-data', [AdminTaxDataController::class, 'store'])->name('users.tax-data.store');
    Route::patch('/users/tax-data/{taxData}', [AdminTaxDataController::class, 'update'])->name('users.tax-data.update');
    Route::delete('/users/tax-data/{taxData}', [AdminTaxDataController::class, 'destroy'])->name('users.tax-data.destroy');

    // Tiendas
    Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
    Route::get('/shops/export-pdf', [AdminShopController::class, 'exportPdf'])->name('shops.export-pdf');
    Route::patch('/shops/{shop}/approve', [AdminShopController::class, 'approve'])->name('shops.approve');
    Route::patch('/shops/{shop}/reject', [AdminShopController::class, 'reject'])->name('shops.reject');
    Route::get('/shops/{shop}', [AdminShopController::class, 'show'])->name('shops.show');

    // Pedidos
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/export-pdf', [AdminOrderController::class, 'exportPdf'])->name('orders.export-pdf');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
});

/*
|--------------------------------------------------------------------------
| Rutas de autenticación (Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';