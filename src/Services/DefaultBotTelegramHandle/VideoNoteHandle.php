<?php

namespace App\Services\DefaultBotTelegramHandle;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class VideoNoteHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $infoVideoNote = $this->telegramService->data['video_note'] ?? null;
        $fileName = $infoVideoNote['file_name'] ?? "";
        $mimeType = $infoVideoNote['mime_type'] ?? "";
        //duration sec:  $infoVideo['duration']
        //$saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($infoVideoNote['file_id']);
    }
}
