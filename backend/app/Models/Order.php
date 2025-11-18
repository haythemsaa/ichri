<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'delivery_method',
        'subtotal',
        'tax_amount',
        'delivery_fee',
        'discount_amount',
        'total_amount',
        'currency',
        'delivery_address',
        'delivery_city',
        'delivery_region',
        'delivery_postal_code',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_instructions',
        'delivery_date',
        'delivered_at',
        'driver_id',
        'tracking_number',
        'notes',
        'admin_notes',
        'confirmed_at',
        'prepared_at',
        'shipped_at',
        'cancelled_at',
        'cancellation_reason',
        'refunded_at',
        'refund_amount',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:3',
            'tax_amount' => 'decimal:3',
            'delivery_fee' => 'decimal:3',
            'discount_amount' => 'decimal:3',
            'total_amount' => 'decimal:3',
            'refund_amount' => 'decimal:3',
            'delivery_date' => 'datetime',
            'delivered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'prepared_at' => 'datetime',
            'shipped_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_number', 'status', 'payment_status', 'total_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Order status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    /**
     * Payment status constants
     */
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';
    const PAYMENT_CREDIT = 'credit';

    /**
     * Payment method constants
     */
    const PAYMENT_CASH = 'cash';
    const PAYMENT_CARD = 'card';
    const PAYMENT_MOBILE = 'mobile';
    const PAYMENT_CREDIT = 'credit';
    const PAYMENT_TRANSFER = 'transfer';

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePreparing($query)
    {
        return $query->where('status', self::STATUS_PREPARING);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_PREPARING => 'En préparation',
            self::STATUS_READY => 'Prête',
            self::STATUS_SHIPPED => 'En livraison',
            self::STATUS_DELIVERED => 'Livrée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_FAILED => 'Échec',
        ][$this->status] ?? 'Inconnu';
    }

    public function getStatusColorAttribute()
    {
        return [
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_PREPARING => 'blue',
            self::STATUS_READY => 'purple',
            self::STATUS_SHIPPED => 'indigo',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_FAILED => 'red',
        ][$this->status] ?? 'gray';
    }

    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    public function getCanBeRefundedAttribute()
    {
        return $this->status === self::STATUS_DELIVERED
            && $this->payment_status === self::PAYMENT_PAID
            && $this->delivered_at->gt(now()->subDays(7));
    }

    /**
     * Helper methods
     */
    public function updateStatus($status, $notes = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;

        // Update timestamp fields
        switch ($status) {
            case self::STATUS_CONFIRMED:
                $this->confirmed_at = now();
                break;
            case self::STATUS_PREPARING:
                $this->prepared_at = now();
                break;
            case self::STATUS_SHIPPED:
                $this->shipped_at = now();
                break;
            case self::STATUS_DELIVERED:
                $this->delivered_at = now();
                break;
            case self::STATUS_CANCELLED:
                $this->cancelled_at = now();
                break;
        }

        $this->save();

        // Log status change
        $this->statusHistories()->create([
            'from_status' => $oldStatus,
            'to_status' => $status,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);

        // Trigger events
        event(new \App\Events\OrderStatusChanged($this, $oldStatus, $status));

        return $this;
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->items()->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $this->tax_amount = $this->subtotal * ($this->tax_rate ?? 0) / 100;

        // Free delivery if subtotal >= threshold
        $freeDeliveryThreshold = config('app.free_delivery_threshold', 0);
        $this->delivery_fee = $this->subtotal >= $freeDeliveryThreshold
            ? 0
            : config('app.delivery_fee', 0);

        $this->total_amount = $this->subtotal + $this->tax_amount + $this->delivery_fee - $this->discount_amount;

        $this->save();

        return $this;
    }

    public function generateOrderNumber()
    {
        $this->order_number = 'ICH' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        $this->save();

        return $this->order_number;
    }

    public function cancel($reason = null)
    {
        if (!$this->can_be_cancelled) {
            return false;
        }

        $this->cancellation_reason = $reason;
        $this->updateStatus(self::STATUS_CANCELLED);

        // Restore stock
        foreach ($this->items as $item) {
            $item->product->incrementStock($item->quantity);
        }

        // Refund if paid
        if ($this->payment_status === self::PAYMENT_PAID) {
            $this->refund();
        }

        return true;
    }

    public function refund($amount = null)
    {
        $this->refund_amount = $amount ?? $this->total_amount;
        $this->payment_status = self::PAYMENT_REFUNDED;
        $this->refunded_at = now();
        $this->save();

        // Process refund through payment gateway
        event(new \App\Events\OrderRefunded($this));

        return $this;
    }
}
