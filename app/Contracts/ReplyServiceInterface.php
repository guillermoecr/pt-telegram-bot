<?php

namespace App\Contracts;

interface ReplyServiceInterface
{
    /**
     * mensaje de respuesta.
     */
    public function generateReply(string $incomingMessage): string;
}