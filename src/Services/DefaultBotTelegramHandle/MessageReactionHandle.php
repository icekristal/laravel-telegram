<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class MessageReactionHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);

    }

    public function handle(): void
    {
        $messageReaction = $this->telegramService->messageReaction['data'] ?? '';
    }
}
