<?php

namespace Icekristal\LaravelTelegram\Services\DefaultBotTelegramHandle;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;

class PhotoHandle extends MainTelegramHandle
{
    public function __construct($data, $botInfo)
    {
        parent::__construct($data, $botInfo);
        $infoPhoto = $data['photo'] ?? null;
        if (is_array($infoPhoto)) {
            $smallPhoto = $infoPhoto[0];
            $fullPhoto = $infoPhoto[1];

            //file size $smallPhoto['file_size']
            //$saveAndGetPathFile = (new IceTelegramService($botInfo))->getPathFile($fullPhoto['file_id']);
        }
    }
}
