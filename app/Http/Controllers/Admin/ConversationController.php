<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConversationController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function index()
    {
        $conversations = Conversation::with('chat')
            ->orderByDesc('last_message_at')
            ->paginate(10);

        return view('admin.conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        // Cargar mensajes ordenados por antigüedad
        $messages = $conversation->messages()
            ->with('chat') 
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return view('admin.conversations.show', compact('conversation', 'messages'));
    }

    public function sendMessageToContact(Request $request, Conversation $conversation)
    {
        // Verificacion del txt
        try {
            $validated = $request->validate([
                'text' => ['required', 'string', 'max:4096'],
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $messageText = $validated['text'];
        $telegramId = $conversation->chat->telegram_id;

        // Envia a TLG
        $response = $this->telegramService->sendMessage($telegramId, $messageText);

        if ($response === null) {
            return back()->with('error', 'Error al enviar el mensaje a Telegram. Verifica logs.');
        }

        // Guard el msje saliente en BBDD
        Message::create([
            'conversation_id' => $conversation->id,
            'telegram_chat_id' => $conversation->chat->id,
            'type' => 'outbound',
            'text' => $messageText,
        ]);

        return back()->with('success', 'Mensaje enviado y registrado correctamente.')->withFragment('messages');
    }
}