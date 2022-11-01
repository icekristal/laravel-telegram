<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class LocationHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $infoLocation = $this->telegramService->data['location'] ?? null;
        $latitude = $infoVoice['latitude'] ?? "";
        $longitude = $infoVoice['longitude'] ?? "";
        $live_period = $infoVoice['live_period'] ?? "";
    }
}
