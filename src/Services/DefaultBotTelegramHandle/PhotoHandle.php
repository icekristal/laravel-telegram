<?php

namespace App\Services\DefaultBotTelegramHandle;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class PhotoHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $infoPhoto = $this->telegramService->data['photo'] ?? null;
        if (is_array($infoPhoto)) {
            $smallPhoto = $infoPhoto[0];
            $fullPhoto = $infoPhoto[1];

            //file size $smallPhoto['file_size']
            //$saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($fullPhoto['file_id']);
        }
    }
}
