<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class CallbackQueryHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $callbackQuery = $this->telegramService->callbackQuery['data'] ?? '';
    }
}
