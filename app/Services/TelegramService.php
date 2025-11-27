<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\TelegramChat;
use App\Models\Conversation;
use App\Models\Message;
use App\Contracts\ReplyServiceInterface;

class TelegramService
{
    protected string $apiUrl;
    protected ReplyServiceInterface $replyService;

    public function __construct(ReplyServiceInterface $replyService)
    {
        $this->apiUrl = Config::get('services.telegram.api_url');
        $this->replyService = $replyService;
    }

    public function handleIncomingUpdate(array $payload): void
    {
        if (!isset($payload['message']) || !isset($payload['message']['text'])) {
            return;
        }

        $messageData = $payload['message'];
        $chatData = $messageData['chat'];
        $incomingText = $messageData['text'];

        // 1. Guardo o Actualizo los datos del chat 
        $chat = TelegramChat::updateOrCreate(
            ['telegram_id' => $chatData['id']],
            [
                'type' => $chatData['type'] ?? 'private',
                'username' => $chatData['username'] ?? null,
                'first_name' => $chatData['first_name'] ?? null,
                'last_name' => $chatData['last_name'] ?? null,
            ]
        );

        // 2. Obtengo y creo la Conversacin 
        $conversation = Conversation::firstOrCreate(
            ['telegram_chat_id' => $chat->id],
            ['status' => 'active', 'last_message_at' => now()]
        );
        $conversation->touch();

        // 3. Guardo el Mensaje entrante
        Message::create([
            'conversation_id' => $conversation->id,
            'telegram_chat_id' => $chat->id,
            'type' => 'inbound',
            'text' => $incomingText,
            'telegram_message_id' => $messageData['message_id'] ?? null,
        ]);

        // 4. Genero respuesta usando la Interfaz DIP
        $replyText = $this->replyService->generateReply($incomingText);

        // 5. Envio a TLG
        $this->sendMessage($chat->telegram_id, $replyText);

        // 6. Guardo la respuesta de salida
        Message::create([
            'conversation_id' => $conversation->id,
            'telegram_chat_id' => $chat->id,
            'type' => 'outbound',
            'text' => $replyText,
        ]);
    }

    public function sendMessage(string|int $chatId, string $text): ?array
    {
        $url = $this->apiUrl . 'sendMessage';

        try {
            $response = Http::post($url, ['chat_id' => $chatId, 'text' => $text]);

            if ($response->failed()) {
                // Registro el erro de la API
                Log::error('Telegram API Error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram API Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function setWebhook(string $webhookUrl): bool
    {
        $url = $this->apiUrl . 'setWebhook';
        try {
            return Http::post($url, [
                'url' => $webhookUrl,
                'secret_token' => Config::get('services.telegram.webhook_secret'),
            ])->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}