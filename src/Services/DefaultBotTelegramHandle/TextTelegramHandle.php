<?php

namespace Icekristal\LaravelTelegram\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class TextTelegramHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $text = $this->telegramService->data['text'] ?? '';
    }
}
