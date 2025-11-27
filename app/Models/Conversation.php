<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_chat_id',
        'status',
        'last_message_at',
    ];
    
    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(TelegramChat::class, 'telegram_chat_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest(); 
    }
}