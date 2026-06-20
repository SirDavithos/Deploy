<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Verificar que el usuario haya recibido este producto
        $hasReceived = Auth::user()->orders()
            ->where('status', 'delivered')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })->exists();

        if (!$hasReceived) {
            return back()->with('error', 'Solo los compradores verificados pueden opinar.');
        }

        $existing = Review::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            return back()->with('error', 'Ya has opinado sobre este producto.');
        }

        $product->reviews()->create([
            'user_id' => Auth::id(),
            'rating'  => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('status', 'Reseña publicada correctamente.');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }
        $review->delete();
        return back()->with('status', 'Reseña eliminada.');
    }
}