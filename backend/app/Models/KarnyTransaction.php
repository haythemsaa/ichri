<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KarnyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'karny_customer_id',
        'type',
        'amount',
        'description',
        'due_date',
        'status',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:3',
            'due_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(KarnyCustomer::class, 'karny_customer_id');
    }

    public function reminders()
    {
        return $this->hasMany(KarnyReminder::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }
}
