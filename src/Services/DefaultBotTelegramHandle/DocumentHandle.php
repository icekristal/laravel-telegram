<?php

namespace App\Services\DefaultBotTelegramHandle;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class DocumentHandle extends MainTelegramHandle
{
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);

    }

    public function handle(): void
    {
        $document = $this->telegramService->data['document'] ?? '';
        $fileName = $document['file_name'] ?? '';
        $mimeType = $document['mime_type'] ?? '';
    }
}
