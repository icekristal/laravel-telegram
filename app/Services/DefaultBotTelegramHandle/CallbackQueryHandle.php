<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class CallbackQueryHandle extends MainTelegramHandle
{
    public function __construct($data, $botInfo)
    {
        parent::__construct($data, $botInfo);
        $callbackQuery = $data['data'] ?? '';
    }
}
