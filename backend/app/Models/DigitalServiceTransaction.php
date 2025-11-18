<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DigitalServiceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_ref',
        'service_type',
        'provider',
        'recipient_number',
        'amount',
        'commission',
        'cost',
        'status',
        'external_ref',
        'response_data',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:3',
            'commission' => 'decimal:3',
            'cost' => 'decimal:3',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->transaction_ref) {
                $transaction->transaction_ref = 'DS-' . date('Ymd') . '-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function getServiceNameAttribute()
    {
        $names = [
            'mobile_topup' => 'Recharge Mobile',
            'electricity_bill' => 'Facture STEG',
            'water_bill' => 'Facture SONEDE',
            'internet_bill' => 'Facture Internet',
            'phone_bill' => 'Facture Téléphone',
            'game_card' => 'Carte de Jeu',
            'other' => 'Autre',
        ];

        return $names[$this->service_type] ?? $this->service_type;
    }
}
