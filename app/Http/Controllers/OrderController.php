<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;


class OrderController extends Controller
{

    // Vista previa del checkout (direcciones y NIT)
    public function checkout()
    {
        $user = Auth::user();
        $cartItems = $user->cartItems()->with('product.shop')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        // Agrupar por tienda
        $shops = $cartItems->groupBy(fn($item) => $item->product->shop_id);

        return Inertia::render('Checkout/Index', [
            'cartItems' => $cartItems,
            'addresses' => $user->addresses,
            'taxData'   => $user->taxData,
            'shops'     => $shops,
        ]);
    }

    // Crear el pedido
    public function store(Request $request)
    {
        $user = Auth::user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'El carrito está vacío.');
        }

        $request->validate([
            'address_id'  => 'required|exists:user_addresses,id',
            'tax_data_id' => 'required|exists:user_tax_data,id',
        ]);

        // Agrupar por tienda y crear un pedido por tienda
        $grouped = $cartItems->groupBy(fn($item) => $item->product->shop_id);

        foreach ($grouped as $shopId => $items) {
            $total = $items->sum(fn($i) => $i->product->price * $i->quantity);

            $order = $user->orders()->create([
                'shop_id'     => $shopId,
                'address_id'  => $request->address_id,
                'tax_data_id' => $request->tax_data_id,
                'status'      => 'pending',
                'total'       => $total,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);
                // Reducir stock
                $item->product->decrement('stock', $item->quantity);
            }
        }

        // Vaciar carrito
        $user->cartItems()->delete();

        return redirect()->route('dashboard')->with('status', 'Pedido realizado con éxito.');
    }

    // Historial del comprador
    public function index(Request $request)
    {
        $query = Auth::user()->orders()
            ->with('shop', 'items.product', 'taxData');

        // Filtro por búsqueda (ID, tienda, producto)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhereHas('shop', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('items.product', function($q2) use ($search) {
                    $q2->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por fecha
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtro por monto
        if ($request->filled('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }
        if ($request->filled('max_total')) {
            $query->where('total', '<=', $request->max_total);
        }

        $orders = $query->latest()->paginate(10)->appends($request->except('page'));

        return Inertia::render('Orders/Index', [
            'orders'  => $orders,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'min_total', 'max_total']),
        ]);
    }

    // Actualizar estado del pedido (para el artesano)
    public function update(Request $request, Order $order)
    {
        // Verificar que la tienda pertenece al usuario logueado
        if ($order->shop->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:pending,confirmed,shipped,delivered,cancelled']);
        $order->update(['status' => $request->status]);

        return back()->with('status', 'Estado del pedido actualizado.');
    }
public function invoice(Order $order)
{
    if ($order->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
        abort(403);
    }

    $order->load('items.product', 'shop', 'taxData', 'address', 'buyer');

    $pdf = Pdf::loadView('pdf.invoice', compact('order'));

    return $pdf->download('factura-' . $order->id . '.pdf');
}
}