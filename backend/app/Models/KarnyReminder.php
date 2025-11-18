<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KarnyReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'karny_transaction_id',
        'karny_customer_id',
        'type',
        'scheduled_at',
        'sent_at',
        'status',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(KarnyTransaction::class, 'karny_transaction_id');
    }

    public function customer()
    {
        return $this->belongsTo(KarnyCustomer::class, 'karny_customer_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDue($query)
    {
        return $query->where('scheduled_at', '<=', now())
            ->where('status', 'pending');
    }
}
