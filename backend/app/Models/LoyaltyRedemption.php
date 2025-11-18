<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoyaltyRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'points_spent',
        'status',
        'redemption_code',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'points_spent' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($redemption) {
            if (!$redemption->redemption_code) {
                $redemption->redemption_code = strtoupper(Str::random(12));
            }
            if (!$redemption->expires_at) {
                $redemption->expires_at = now()->addDays(30);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(LoyaltyReward::class, 'reward_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    // Methods
    public function approve()
    {
        $this->update(['status' => 'approved']);
    }

    public function use()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Redemption must be approved before use');
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            throw new \Exception('Redemption code has expired');
        }

        $this->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    public function cancel()
    {
        if ($this->status === 'used') {
            throw new \Exception('Cannot cancel used redemption');
        }

        $this->update(['status' => 'cancelled']);

        // Refund points to user
        $loyaltyPoint = LoyaltyPoint::where('user_id', $this->user_id)->first();
        if ($loyaltyPoint) {
            $loyaltyPoint->addPoints($this->points_spent, 'bonus', 'LoyaltyRedemption', $this->id, 'Refund from cancelled redemption');
        }
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isUsable()
    {
        return $this->status === 'approved' && !$this->isExpired();
    }
}
