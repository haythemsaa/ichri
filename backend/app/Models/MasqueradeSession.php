<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasqueradeSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'target_user_id',
        'reason',
        'started_at',
        'ended_at',
        'ip_address',
        'user_agent',
        'actions_log',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'actions_log' => 'array',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // Helpers
    public function isActive()
    {
        return is_null($this->ended_at);
    }

    public function logAction($action, $details = [])
    {
        $log = $this->actions_log ?? [];
        $log[] = [
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toDateTimeString(),
        ];
        
        $this->update(['actions_log' => $log]);
    }

    public function end()
    {
        $this->update(['ended_at' => now()]);
    }
}
