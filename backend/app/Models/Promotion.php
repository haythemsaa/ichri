<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'config',
        'discount_value',
        'min_purchase',
        'usage_limit_per_user',
        'usage_limit_total',
        'usage_count',
        'start_date',
        'end_date',
        'is_active',
        'is_featured',
        'terms',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'discount_value' => 'decimal:3',
            'min_purchase' => 'decimal:3',
            'usage_limit_per_user' => 'integer',
            'usage_limit_total' => 'integer',
            'usage_count' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_products')->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'promotion_categories')->withTimestamps();
    }

    public function usages()
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Accessors
     */
    public function getIsValidAttribute()
    {
        return $this->is_active
            && $this->start_date <= now()
            && $this->end_date >= now()
            && (!$this->usage_limit_total || $this->usage_count < $this->usage_limit_total);
    }

    /**
     * Helper methods
     */
    public function canBeUsedBy($userId)
    {
        if (!$this->is_valid) {
            return false;
        }

        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()->where('user_id', $userId)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount($orderAmount, $items = [])
    {
        if ($this->min_purchase && $orderAmount < $this->min_purchase) {
            return 0;
        }

        switch ($this->type) {
            case 'percentage':
                return ($orderAmount * $this->discount_value) / 100;

            case 'fixed_amount':
                return min($this->discount_value, $orderAmount);

            case 'bogo':
                // Buy One Get One logic
                return $this->calculateBOGO($items);

            case 'bundle':
                // Bundle deal logic (e.g., 2+1 free)
                return $this->calculateBundle($items);

            case 'tier_pricing':
                // Tier pricing based on quantity
                return $this->calculateTierPricing($items);

            default:
                return 0;
        }
    }

    private function calculateBOGO($items)
    {
        // Implementation of Buy One Get One logic
        $discount = 0;
        foreach ($items as $item) {
            if ($this->products->contains($item['product_id'])) {
                $freeItems = floor($item['quantity'] / 2);
                $discount += $freeItems * $item['unit_price'];
            }
        }
        return $discount;
    }

    private function calculateBundle($items)
    {
        // Implementation of bundle deal (e.g., buy 2 get 1 free)
        $config = $this->config;
        $buyQuantity = $config['buy'] ?? 2;
        $getQuantity = $config['get'] ?? 1;

        $discount = 0;
        foreach ($items as $item) {
            if ($this->products->contains($item['product_id'])) {
                $sets = floor($item['quantity'] / ($buyQuantity + $getQuantity));
                $discount += $sets * $getQuantity * $item['unit_price'];
            }
        }
        return $discount;
    }

    private function calculateTierPricing($items)
    {
        // Implementation of tier pricing
        // Example: buy 10-20 get 5% off, 20+ get 10% off
        return 0; // Implement based on config
    }

    public function recordUsage($userId, $orderId, $discountAmount)
    {
        $this->usages()->create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
        ]);

        $this->increment('usage_count');
    }
}
