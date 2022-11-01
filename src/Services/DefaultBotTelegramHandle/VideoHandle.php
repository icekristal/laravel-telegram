<?php

namespace App\Services\DefaultBotTelegramHandle;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class VideoHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $infoVideo = $this->telegramService->data['video'] ?? null;
        $fileName = $infoVideo['file_name'] ?? "";
        $mimeType = $infoVideo['mime_type'] ?? "";
        //duration sec:  $infoVideo['duration']
        //$saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($infoVideo['file_id']);
    }
}
