<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user's cart
     */
    public function index()
    {
        $cartItems = auth()->user()
            ->cart()
            ->with('product.images')
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->current_price;
        });

        $freeDeliveryThreshold = config('app.free_delivery_threshold', 0);
        $deliveryFee = $subtotal >= $freeDeliveryThreshold ? 0 : config('app.delivery_fee', 0);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'items_count' => $cartItems->sum('quantity'),
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_in_stock || $product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant'
            ], 400);
        }

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => \DB::raw("quantity + {$request->quantity}")
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'data' => $cartItem->load('product.images')
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem = CartItem::where('user_id', auth()->id())
            ->findOrFail($id);

        if ($cartItem->product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant'
            ], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Panier mis à jour',
            'data' => $cartItem->load('product.images')
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove($id)
    {
        $cartItem = CartItem::where('user_id', auth()->id())
            ->findOrFail($id);

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré du panier'
        ]);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        auth()->user()->cart()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Panier vidé'
        ]);
    }
}
