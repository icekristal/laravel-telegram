<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class TextTelegramHandle extends MainTelegramHandle
{
    public $text = '';
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $this->text = $this->telegramService->data['text'] ?? '';
    }
}
