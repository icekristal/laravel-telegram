<?php

namespace Icekristal\LaravelTelegram\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class VoiceHandle extends MainTelegramHandle
{
    public function __construct($data, $botInfo)
    {
        parent::__construct($data, $botInfo);
        $infoVoice = $data['voice'] ?? null;
        $mimeType = $infoVoice['mime_type'] ?? "";
        //$saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($infoVoice['file_id']);
    }
}
