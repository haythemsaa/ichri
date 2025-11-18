<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'whatsapp_id',
        'status',
        'context',
        'last_message_at',
        'expires_at',
    ];

    protected $casts = [
        'context' => 'array',
        'last_message_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function orders()
    {
        return $this->hasMany(WhatsAppOrder::class, 'conversation_id');
    }

    public function isActive()
    {
        return $this->status === 'active' && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function updateContext($key, $value)
    {
        $context = $this->context ?? [];
        $context[$key] = $value;
        $this->update(['context' => $context]);
    }

    public function getContext($key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }
}
