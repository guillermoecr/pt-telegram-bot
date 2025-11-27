<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Env;
use App\Models\TelegramChat;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function webhook_rejects_requests_without_correct_secret(): void
    {
        // Asegura que el secret esta cargado para la validación de la ruta
        $secret = Env::get('TELEGRAM_WEBHOOK_SECRET', 'mi-clave-secreta-random-12345'); 
        Config::set('services.telegram.webhook_secret', $secret);
        
        $wrongSecret = 'not-the-right-secret';

        $response = $this->postJson("/api/telegram/webhook/{$wrongSecret}", [
            'update_id' => 12345,
        ]);

        $response->assertStatus(403); 
    }

    /** @test */
    public function webhook_accepts_valid_requests_but_fails_without_implementation(): void
    {
        // Asegura que el secret est cargado para la validación de la ruta
        $secret = Env::get('TELEGRAM_WEBHOOK_SECRET', 'mi-clave-secreta-random-12345'); 
        Config::set('services.telegram.webhook_secret', $secret);
        
        $payload = [
            'update_id' => 10000,
            'message' => [
                'message_id' => 123,
                'date' => time(),
                'text' => 'Hola bot',
                'chat' => [
                    'id' => 1806694797,
                    'type' => 'private',
                    'first_name' => 'Guillermo',
                ],
            ],
        ];

        $response = $this->postJson("/api/telegram/webhook/{$secret}", $payload, [
            'X-Telegram-Bot-Api-Secret-Token' => $secret, 
        ]);

        $response->assertOk(); 
    }

    /** @test */
    public function webhook_persists_incoming_text_message(): void
    {
        $secret = Env::get('TELEGRAM_WEBHOOK_SECRET', 'mi-clave-secreta-random-12345'); 
        Config::set('services.telegram.webhook_secret', $secret);
        
        $payload = [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 555,
                'from' => [
                    'id' => 999888777,
                    'is_bot' => false,
                    'first_name' => 'Juan',
                    'username' => 'juanperez',
                ],
                'chat' => [
                    'id' => 999888777,
                    'first_name' => 'Juan',
                    'username' => 'juanperez',
                    'type' => 'private',
                ],
                'date' => time(),
                'text' => 'Hola, quiero info sobre el producto',
            ],
        ];

        $response = $this->postJson("/api/telegram/webhook/{$secret}", $payload);

        $response->assertOk();

        // checa persistencia de Chat
        $this->assertDatabaseHas('telegram_chats', [
            'telegram_id' => 999888777,
            'username' => 'juanperez',
        ]);

        // Verifica persistencia de Mensaje
        $this->assertDatabaseHas('messages', [
            'telegram_message_id' => 555,
            'text' => 'Hola, quiero info sobre el producto',
            'type' => 'inbound',
        ]);
        
        $chat = TelegramChat::where('telegram_id', 999888777)->first();
        $this->assertDatabaseHas('conversations', [
            'telegram_chat_id' => $chat->id,
        ]);
    }

    /** @test */
    public function webhook_replies_automatically_to_incoming_message(): void
    {
        // simulo la respuesta externa (sin HTTP real)
        \Illuminate\Support\Facades\Http::fake([
            'api.telegram.org*' => \Illuminate\Support\Facades\Http::response(['ok' => true], 200),
        ]);

        $secret = Env::get('TELEGRAM_WEBHOOK_SECRET', 'mi-clave-secreta-random-12345'); 
        Config::set('services.telegram.webhook_secret', $secret);
        
        $telegramId = 12345; 

        $payload = [
            'update_id' => 999,
            'message' => [
                'message_id' => 888,
                'date' => time(),
                'text' => 'Hola bot',
                'from' => ['id' => $telegramId, 'first_name' => 'Test', 'is_bot' => false],
                'chat' => ['id' => $telegramId, 'type' => 'private', 'first_name' => 'Test'],
            ],
        ];

        $this->postJson("/api/telegram/webhook/{$secret}", $payload);

        // Obtener el chat interno creado para verificar la FK
        $chat = TelegramChat::where('telegram_id', $telegramId)->first();

        // Verificar que se guardó el mensaje de RESPUESTA (Outbound)
        $this->assertDatabaseHas('messages', [
            'telegram_chat_id' => $chat->id, 
            'type' => 'outbound', 
        ]);

        // Verificar si se llamo bien a la API externa
        \Illuminate\Support\Facades\Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($telegramId) {
            return $request->url() == Config::get('services.telegram.api_url') . 'sendMessage' &&
                   $request['chat_id'] == $telegramId;
        });
    }
}