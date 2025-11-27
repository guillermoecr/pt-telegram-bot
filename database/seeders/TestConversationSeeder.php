<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TelegramChat;
use App\Models\Conversation;
use App\Models\Message;

class TestConversationSeeder extends Seeder
{
    public function run(): void
    {
        // Crea un contacto de prueba
        $chat = TelegramChat::firstOrCreate(
            ['telegram_id' => 999888777],
            [
                'type' => 'private',
                'username' => 'juanperez',
                'first_name' => 'Juan',
            ]
        );

        // Crea su conversación asociada
        $conversation = Conversation::firstOrCreate(
            ['telegram_chat_id' => $chat->id],
            ['status' => 'active', 'last_message_at' => now()]
        );

        // Mensaje entrante
        Message::create([
            'conversation_id' => $conversation->id,
            'telegram_chat_id' => $chat->id,
            'type' => 'inbound',
            'text' => 'Hola, necesito ayuda con mi pedido.',
        ]);
        
        // Mesaje de salida
        Message::create([
            'conversation_id' => $conversation->id,
            'telegram_chat_id' => $chat->id,
            'type' => 'outbound',
            'text' => '¡Hola! Ya te asignamos a un agente.',
        ]);
    }
}