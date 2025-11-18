<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
        'lifetime_points',
        'tier',
        'tier_expires_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'lifetime_points' => 'integer',
        'tier_expires_at' => 'datetime',
    ];

    const TIER_THRESHOLDS = [
        'bronze' => 0,
        'silver' => 1000,
        'gold' => 5000,
        'platinum' => 15000,
    ];

    const TIER_MULTIPLIERS = [
        'bronze' => 1.0,
        'silver' => 1.2,
        'gold' => 1.5,
        'platinum' => 2.0,
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'user_id', 'user_id');
    }

    // Methods
    public function addPoints($points, $type = 'earn', $source_type = null, $source_id = null, $description = null)
    {
        $multiplier = self::TIER_MULTIPLIERS[$this->tier] ?? 1.0;
        $earnedPoints = (int)($points * $multiplier);

        $this->increment('points', $earnedPoints);
        $this->increment('lifetime_points', $earnedPoints);

        // Check for tier upgrade
        $this->checkTierUpgrade();

        // Create transaction
        LoyaltyTransaction::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'points' => $earnedPoints,
            'balance_after' => $this->points,
            'source_type' => $source_type,
            'source_id' => $source_id,
            'description' => $description,
        ]);

        return $earnedPoints;
    }

    public function redeemPoints($points, $reward_id = null, $description = null)
    {
        if ($this->points < $points) {
            throw new \Exception('Insufficient points');
        }

        $this->decrement('points', $points);

        LoyaltyTransaction::create([
            'user_id' => $this->user_id,
            'type' => 'redeem',
            'points' => -$points,
            'balance_after' => $this->points,
            'source_type' => $reward_id ? 'LoyaltyReward' : null,
            'source_id' => $reward_id,
            'description' => $description,
        ]);

        return true;
    }

    protected function checkTierUpgrade()
    {
        $currentTier = $this->tier;
        $newTier = $this->calculateTier($this->lifetime_points);

        if ($newTier !== $currentTier) {
            $this->update([
                'tier' => $newTier,
                'tier_expires_at' => now()->addYear(),
            ]);
        }
    }

    protected function calculateTier($points)
    {
        if ($points >= self::TIER_THRESHOLDS['platinum']) {
            return 'platinum';
        } elseif ($points >= self::TIER_THRESHOLDS['gold']) {
            return 'gold';
        } elseif ($points >= self::TIER_THRESHOLDS['silver']) {
            return 'silver';
        }
        return 'bronze';
    }

    public function getTierMultiplier()
    {
        return self::TIER_MULTIPLIERS[$this->tier] ?? 1.0;
    }
}
