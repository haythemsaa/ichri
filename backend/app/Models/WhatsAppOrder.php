<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'order_id',
        'user_id',
        'phone_number',
        'status',
        'items',
        'total_amount',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:3',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Methods
    public function confirm()
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Only pending orders can be confirmed');
        }

        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Create actual order in system
        $this->createSystemOrder();
    }

    public function cancel($reason = null)
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            throw new \Exception('Cannot cancel completed or already cancelled orders');
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function complete()
    {
        if ($this->status !== 'confirmed') {
            throw new \Exception('Only confirmed orders can be completed');
        }

        $this->update(['status' => 'completed']);
    }

    protected function createSystemOrder()
    {
        if ($this->order_id) {
            return; // Order already created
        }

        // TODO: Implement actual order creation
        // This would create a real Order model instance
        // For now, we just store the reference
    }

    public function addItem($productId, $productName, $quantity, $price)
    {
        $items = $this->items ?? [];

        $items[] = [
            'product_id' => $productId,
            'product_name' => $productName,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $quantity * $price,
        ];

        $this->update([
            'items' => $items,
            'total_amount' => collect($items)->sum('total'),
        ]);
    }

    public function removeItem($index)
    {
        $items = $this->items ?? [];

        if (isset($items[$index])) {
            unset($items[$index]);
            $items = array_values($items); // Reindex array

            $this->update([
                'items' => $items,
                'total_amount' => collect($items)->sum('total'),
            ]);
        }
    }

    public function clearItems()
    {
        $this->update([
            'items' => [],
            'total_amount' => 0,
        ]);
    }

    public function getItemsCount()
    {
        return count($this->items ?? []);
    }

    public function getItemsQuantity()
    {
        return collect($this->items ?? [])->sum('quantity');
    }
}
