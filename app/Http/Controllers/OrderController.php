<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrderController extends Controller
{
    public function checkout()
    {
        $user = Auth::user();
        $cartItems = $user->cartItems()->with('product.shop')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $shops = $cartItems->groupBy(fn($item) => $item->product->shop_id);

        return Inertia::render('Checkout/Index', [
            'cartItems' => $cartItems,
            'addresses' => $user->addresses,
            'taxData'   => $user->taxData,
            'shops'     => $shops,
        ]);
    }

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
                $item->product->decrement('stock', $item->quantity);
            }
        }

        $user->cartItems()->delete();

        return redirect()->route('dashboard')->with('status', 'Pedido realizado con éxito.');
    }

    public function index(Request $request)
    {
        $query = Auth::user()->orders()
            ->with('shop.taxData', 'items.product', 'taxData');

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
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

    public function update(Request $request, Order $order)
    {
        if ($order->shop->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
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

        $order->load('items.product', 'shop.taxData', 'taxData', 'address', 'buyer');

        // Datos para el QR (formato SIN)
        $qrData = [
            'tipo'       => $order->shop->taxData->first()?->tax_regimen === 'Régimen General' ? 'FACTURA' : 'RECIBO',
            'pedido'     => $order->id,
            'fecha'      => $order->created_at->format('Y-m-d H:i:s'),
            'nit_emisor' => $order->shop->taxData->first()?->nit_or_ci ?? 'N/A',
            'razon_social' => $order->shop->taxData->first()?->business_name ?? 'N/A',
            'comprador'  => $order->buyer->first_name . ' ' . $order->buyer->paternal_last_name,
            'total'      => number_format($order->total, 2) . ' BOB',
        ];

        $qr = QrCode::size(120)->generate(json_encode($qrData));

        $pdf = Pdf::loadView('pdf.invoice', compact('order', 'qr'));

        return $pdf->download('documento-' . $order->id . '.pdf');
    }
}