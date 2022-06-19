<?php

namespace App\Services\DefaultBotTelegramHandle;


use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class TextTelegramHandle extends MainTelegramHandle
{
    public function __construct($data, $botInfo)
    {
        parent::__construct($data, $botInfo);
        $text = $data['text'] ?? '';
    }
}
