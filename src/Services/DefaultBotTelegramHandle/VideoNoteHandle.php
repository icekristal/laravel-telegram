<?php

namespace Icekristal\LaravelTelegram\Services\DefaultBotTelegramHandle;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class VideoNoteHandle extends MainTelegramHandle
{
    public function __construct($data, $botInfo)
    {
        parent::__construct($data, $botInfo);
        $infoVideoNote = $data['video_note'] ?? null;
        $fileName = $infoVideoNote['file_name'] ?? "";
        $mimeType = $infoVideoNote['mime_type'] ?? "";
        //duration sec:  $infoVideo['duration']
        //$saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($infoVideoNote['file_id']);
    }
}
