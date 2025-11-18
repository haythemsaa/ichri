<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'role',
        'permissions',
        'spending_limit',
        'is_active',
        'invited_by',
        'invited_at',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'spending_limit' => 'decimal:3',
        'is_active' => 'boolean',
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    // Relationships
    public function account()
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(TeamActivityLog::class);
    }

    // Permissions check
    public function hasPermission($permission)
    {
        if ($this->role === 'owner' || $this->role === 'admin') {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    public function canOrder()
    {
        return in_array($this->role, ['owner', 'admin', 'manager', 'employee']);
    }

    public function canManageTeam()
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function canViewReports()
    {
        return $this->role !== 'employee' || $this->hasPermission('view_reports');
    }

    public function isWithinSpendingLimit($amount)
    {
        if (!$this->spending_limit) {
            return true;
        }

        // Get total spending this month
        $monthlySpending = Order::where('user_id', $this->user_id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_amount');

        return ($monthlySpending + $amount) <= $this->spending_limit;
    }
}
