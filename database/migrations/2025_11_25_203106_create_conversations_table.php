<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            // FK al chat de Telegram. Borra conversación si el chat es eliminado.
            $table->foreignId('telegram_chat_id')->constrained('telegram_chats')->cascadeOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            // Un chat solo puede tener una conversación activa
            $table->unique('telegram_chat_id'); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};