<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KarnyCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'qr_code',
        'credit_limit',
        'current_balance',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:3',
            'current_balance' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (!$customer->qr_code) {
                $customer->qr_code = 'KARNY-' . strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(KarnyTransaction::class);
    }

    public function reminders()
    {
        return $this->hasMany(KarnyReminder::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOverLimit($query)
    {
        return $query->whereColumn('current_balance', '>', 'credit_limit');
    }

    /**
     * Accessors
     */
    public function getCreditAvailableAttribute()
    {
        return max(0, $this->credit_limit - $this->current_balance);
    }

    public function getIsOverLimitAttribute()
    {
        return $this->current_balance > $this->credit_limit;
    }

    public function getPendingPaymentsAttribute()
    {
        return $this->transactions()
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function getOverduePaymentsAttribute()
    {
        return $this->transactions()
            ->where('type', 'credit')
            ->where('status', 'overdue')
            ->sum('amount');
    }

    /**
     * Helper methods
     */
    public function addCredit($amount, $description = null, $dueDate = null)
    {
        // Créer la transaction de crédit
        $transaction = $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'due_date' => $dueDate ?? now()->addDays(7),
            'status' => 'pending',
        ]);

        // Mettre à jour le solde
        $this->increment('current_balance', $amount);

        // Programmer un rappel 1 jour avant échéance
        if ($dueDate) {
            $transaction->reminders()->create([
                'karny_customer_id' => $this->id,
                'type' => 'sms',
                'scheduled_at' => $dueDate->subDay(),
                'message' => "Rappel: Paiement de {$amount} TND dû demain pour {$this->name}",
            ]);
        }

        return $transaction;
    }

    public function addPayment($amount, $method = 'cash', $transactionId = null)
    {
        // Créer la transaction de paiement
        $payment = $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'payment',
            'amount' => $amount,
            'description' => "Paiement reçu",
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $method,
        ]);

        // Mettre à jour le solde
        $this->decrement('current_balance', $amount);

        // Marquer les transactions comme payées (FIFO)
        if ($transactionId) {
            $transaction = $this->transactions()->find($transactionId);
            if ($transaction) {
                $transaction->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $method,
                ]);
            }
        } else {
            $this->autoPay($amount);
        }

        return $payment;
    }

    private function autoPay($amount)
    {
        $remaining = $amount;
        $pendingTransactions = $this->transactions()
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get();

        foreach ($pendingTransactions as $transaction) {
            if ($remaining <= 0) break;

            if ($remaining >= $transaction->amount) {
                $transaction->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                $remaining -= $transaction->amount;
            }
        }
    }

    public function checkOverdue()
    {
        $overdueTransactions = $this->transactions()
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        return $overdueTransactions;
    }
}
