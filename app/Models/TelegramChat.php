<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TelegramChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'type',
        'username',
        'first_name',
        'last_name',
    ];

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class, 'telegram_chat_id');
    }
}