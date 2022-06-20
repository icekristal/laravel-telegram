<?php

namespace Icekristal\LaravelTelegram\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class VoiceHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $infoVoice = $this->telegramService->data['voice'] ?? null;
        $mimeType = $infoVoice['mime_type'] ?? "";
        //$saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($infoVoice['file_id']);
    }
}
