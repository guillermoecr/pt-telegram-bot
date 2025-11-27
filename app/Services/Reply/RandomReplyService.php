<?php

namespace App\Services\Reply;

use App\Contracts\ReplyServiceInterface;

class RandomReplyService implements ReplyServiceInterface
{
    protected array $randomReplies = [
        '¡Hola! Gracias por escribirnos.',
        'En este momento estamos ocupados, pero te responderemos pronto.',
        '¿Podrías darme más detalles?',
        'Interesante... cuéntame más.',
        'Soy un bot de Laravel 11, ¡funciono de maravilla!',
    ];

    public function generateReply(string $incomingMessage): string
    {
        // devolvo una respuesta aleatoria (logica bonus IA simple)
        return $this->randomReplies[array_rand($this->randomReplies)];
    }
}