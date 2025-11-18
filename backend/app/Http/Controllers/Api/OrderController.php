<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get user's orders
     */
    public function index(Request $request)
    {
        $orders = Order::forUser(auth()->id())
            ->with(['items.product', 'driver'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get order by ID
     */
    public function show($id)
    {
        $order = Order::forUser(auth()->id())
            ->with(['items.product.images', 'driver', 'payment', 'delivery', 'statusHistories'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Create new order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string',
            'delivery_city' => 'required|string',
            'delivery_region' => 'nullable|string',
            'delivery_latitude' => 'nullable|numeric',
            'delivery_longitude' => 'nullable|numeric',
            'delivery_instructions' => 'nullable|string',
            'payment_method' => 'required|in:cash,card,mobile,credit,transfer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => $request->payment_method === 'credit'
                    ? Order::PAYMENT_CREDIT
                    : Order::PAYMENT_PENDING,
                'payment_method' => $request->payment_method,
                'delivery_method' => 'standard',
                'delivery_address' => $request->delivery_address,
                'delivery_city' => $request->delivery_city,
                'delivery_region' => $request->delivery_region,
                'delivery_latitude' => $request->delivery_latitude,
                'delivery_longitude' => $request->delivery_longitude,
                'delivery_instructions' => $request->delivery_instructions,
                'notes' => $request->notes,
                'currency' => 'TND',
            ]);

            // Add items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Stock insuffisant pour {$product->name}");
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->current_price,
                    'total_price' => $product->current_price * $item['quantity'],
                ]);

                // Decrement stock
                $product->decrementStock($item['quantity']);
            }

            // Calculate totals
            $order->calculateTotals();

            // Generate order number
            $order->generateOrderNumber();

            // Confirm order
            $order->updateStatus(Order::STATUS_CONFIRMED);

            // Clear cart
            $user->cart()->delete();

            DB::commit();

            // Send notifications
            // event(new \App\Events\OrderCreated($order));

            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'data' => $order->load(['items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancel order
     */
    public function cancel($id, Request $request)
    {
        $order = Order::forUser(auth()->id())->findOrFail($id);

        if (!$order->can_be_cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut pas être annulée'
            ], 400);
        }

        $order->cancel($request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée avec succès',
            'data' => $order
        ]);
    }

    /**
     * Reorder (create new order from existing one)
     */
    public function reorder($id)
    {
        $order = Order::forUser(auth()->id())->findOrFail($id);

        $items = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'delivery_address' => $order->delivery_address,
                'delivery_city' => $order->delivery_city,
            ]
        ]);
    }
}
