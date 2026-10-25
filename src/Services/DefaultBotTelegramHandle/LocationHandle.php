<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class LocationHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);

    }

    public function handle(): void
    {
        $infoLocation = $this->telegramService->data['location'] ?? null;
        $latitude = $infoLocation['latitude'] ?? "";
        $longitude = $infoLocation['longitude'] ?? "";
        $live_period = $infoLocation['live_period'] ?? "";
    }
}
