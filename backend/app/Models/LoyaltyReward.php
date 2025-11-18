<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'points_cost',
        'config',
        'quantity_available',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'config' => 'array',
        'points_cost' => 'integer',
        'quantity_available' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    // Relationships
    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class, 'reward_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }

    public function scopeAvailable($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('quantity_available')
                  ->orWhere('quantity_available', '>', 0);
            });
    }

    // Methods
    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        if ($this->quantity_available !== null && $this->quantity_available <= 0) {
            return false;
        }

        return true;
    }

    public function decrementQuantity()
    {
        if ($this->quantity_available !== null) {
            $this->decrement('quantity_available');
        }
    }

    public function getDiscountAmount()
    {
        if ($this->type === 'discount' && isset($this->config['amount'])) {
            return $this->config['amount'];
        }
        return 0;
    }

    public function getDiscountPercentage()
    {
        if ($this->type === 'discount' && isset($this->config['percentage'])) {
            return $this->config['percentage'];
        }
        return 0;
    }
}
