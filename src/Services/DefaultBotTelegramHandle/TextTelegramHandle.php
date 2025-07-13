<?php

namespace App\Services\DefaultBotTelegramHandle;

use Icekristal\LaravelTelegram\Facades\TelegramDeeplinkAction;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;
use Illuminate\Support\Str;

class TextTelegramHandle extends MainTelegramHandle
{
    public $text = '';
    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $this->text = $this->telegramService->data['text'] ?? null;

        if (!is_null($this->text) && Str::startsWith($this->text, '/start ')) {
            $start = trim(Str::after($this->text, '/start '));

            if ($decoded = TelegramDeeplinkAction::find($start)?->decoded) {
                $start = $decoded;
            }

            if (Str::containsAll($start, ['OWNER-S', 'OWNER-E'])) {
                $messageOwnerId =  Str::before(Str::after($start, 'OWNER-S'), 'OWNER-E');
            } else {
                $array = explode('-', $start);
            }
        }
    }
}
