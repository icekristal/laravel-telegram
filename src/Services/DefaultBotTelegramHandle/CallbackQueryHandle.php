<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class CallbackQueryHandle extends MainTelegramHandle
{
    public $callbackQuery = null;
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $this->callbackQuery = $this->telegramService->callbackQuery['data'] ?? '';
    }
}
