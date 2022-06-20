<?php

namespace Icekristal\LaravelTelegram\Services\DefaultBotTelegramHandle;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class DocumentHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $document = $this->telegramService->data['document'] ?? '';
        $fileName = $document['file_name'] ?? '';
        $mimeType = $document['mime_type'] ?? '';

        $saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($document['file_id']);
    }
}
