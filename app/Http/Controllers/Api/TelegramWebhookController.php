<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\TelegramService;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, string $secret, TelegramService $telegramService)
    {
        $expectedSecret = Config::get('services.telegram.webhook_secret');

        // 1. Verificacion de seguridad
        if ($secret !== $expectedSecret) {
            Log::warning('Webhook unauthorized access attempt.');
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }
        
        // 2. Procesa el payload
        try {
            $telegramService->handleIncomingUpdate($request->all());
        } catch (\Exception $e) {
            // captura la excepcin para garantizar que tlg reciba un 200
            Log::error('Error processing webhook: ' . $e->getMessage());
        }
        
        // Respuesta obligatoria 200 para TLg
        return response()->json(['status' => 'ok']);
    }
}