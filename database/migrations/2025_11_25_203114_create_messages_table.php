<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('telegram_chat_id')->constrained('telegram_chats')->cascadeOnDelete();

            $table->enum('type', ['inbound', 'outbound'])->comment('Si el mensaje viene del chat (inbound) o del bot/panel (outbound)');
            $table->text('text');

            $table->unsignedBigInteger('telegram_message_id')->nullable(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};