<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class DocumentHandle extends MainTelegramHandle
{
    public function __construct($data, $botInfo)
    {
        parent::__construct($data, $botInfo);
        $document = $data['document'] ?? '';
        $fileName = $document['file_name'] ?? '';
        $mimeType = $document['mime_type'] ?? '';

        $saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($document['file_id']);
    }
}
