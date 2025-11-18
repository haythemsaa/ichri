<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone',
        'email',
        'password',
        'first_name',
        'last_name',
        'store_name',
        'store_type',
        'address',
        'city',
        'region',
        'postal_code',
        'latitude',
        'longitude',
        'profile_photo',
        'store_photo',
        'patente_number',
        'cin_number',
        'is_verified',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'credit_score',
        'credit_limit',
        'credit_used',
        'credit_level',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'credit_score' => 'integer',
            'credit_limit' => 'decimal:2',
            'credit_used' => 'decimal:2',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['phone', 'email', 'store_name', 'is_verified', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }

    public function cart()
    {
        return $this->hasMany(CartItem::class);
    }

    public function karnyCustomers()
    {
        return $this->hasMany(KarnyCustomer::class);
    }

    public function karnyTransactions()
    {
        return $this->hasMany(KarnyTransaction::class);
    }

    public function digitalServiceTransactions()
    {
        return $this->hasMany(DigitalServiceTransaction::class);
    }

    // Phase 2 Relationships

    public function loyaltyPoints()
    {
        return $this->hasOne(LoyaltyPoint::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function loyaltyRedemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class, 'account_id');
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class, 'user_id');
    }

    public function sentTeamInvitations()
    {
        return $this->hasMany(TeamInvitation::class, 'invited_by');
    }

    public function receivedTeamInvitations()
    {
        return $this->hasMany(TeamInvitation::class, 'email', 'email')
            ->orWhere('phone', $this->phone);
    }

    public function masqueradeSessions()
    {
        return $this->hasMany(MasqueradeSession::class, 'admin_user_id');
    }

    public function masqueradeTargetSessions()
    {
        return $this->hasMany(MasqueradeSession::class, 'target_user_id');
    }

    public function whatsappConversations()
    {
        return $this->hasMany(WhatsAppConversation::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeInRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Accessors & Mutators
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getCreditAvailableAttribute()
    {
        return $this->credit_limit - $this->credit_used;
    }

    public function getIsEligibleForCreditAttribute()
    {
        return $this->credit_score >= config('app.credit_min_score', 60)
            && $this->is_verified
            && $this->is_active;
    }

    /**
     * Helper methods
     */
    public function updateCreditScore()
    {
        // Calculate credit score based on:
        // - Account age (20%)
        // - Number of orders (15%)
        // - Average basket (10%)
        // - Payment on time rate (30%)
        // - KYC documents (25%)

        $accountAge = $this->created_at->diffInDays(now());
        $accountAgeScore = min($accountAge / 365 * 100, 100) * 0.20;

        $orderCount = $this->orders()->count();
        $orderCountScore = min($orderCount / 50 * 100, 100) * 0.15;

        $avgBasket = $this->orders()->avg('total_amount') ?? 0;
        $avgBasketScore = min($avgBasket / 500 * 100, 100) * 0.10;

        $paidOnTime = $this->creditTransactions()
            ->where('status', 'paid')
            ->where('paid_at', '<=', 'due_date')
            ->count();
        $totalCredit = $this->creditTransactions()->count();
        $paymentScore = $totalCredit > 0 ? ($paidOnTime / $totalCredit * 100) * 0.30 : 0;

        $documentsCount = $this->documents()->where('is_verified', true)->count();
        $documentsScore = min($documentsCount / 3 * 100, 100) * 0.25;

        $totalScore = $accountAgeScore + $orderCountScore + $avgBasketScore + $paymentScore + $documentsScore;

        $this->update(['credit_score' => round($totalScore)]);

        return $this->credit_score;
    }

    public function updateCreditLimit()
    {
        $score = $this->credit_score;

        if ($score >= 80) {
            $this->credit_level = 'platinum';
            $this->credit_limit = 15000;
        } elseif ($score >= 70) {
            $this->credit_level = 'gold';
            $this->credit_limit = 5000;
        } elseif ($score >= 60) {
            $this->credit_level = 'silver';
            $this->credit_limit = 1500;
        } else {
            $this->credit_level = 'bronze';
            $this->credit_limit = 500;
        }

        $this->save();
    }
}
