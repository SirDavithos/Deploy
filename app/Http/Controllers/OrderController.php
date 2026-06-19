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
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with('shop', 'items.product')
            ->latest()
            ->get();

        return Inertia::render('Orders/Index', ['orders' => $orders]);
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

    $order->load('items.product', 'shop', 'taxData', 'buyer');

    // Limpiar cualquier salida previa
    if (ob_get_length()) ob_end_clean();

    $pdf = Pdf::loadView('pdf.invoice', compact('order'));

    return $pdf->download('factura-' . $order->id . '.pdf');
}
}