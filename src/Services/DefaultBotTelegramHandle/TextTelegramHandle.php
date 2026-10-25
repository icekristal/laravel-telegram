<?php

namespace App\Services\DefaultBotTelegramHandle;

use App\Services\Clients;
use Icekristal\LaravelTelegram\Facades\TelegramDeeplinkAction;
use Icekristal\LaravelTelegram\Services\MainTelegramHandle;
use Illuminate\Support\Str;

class TextTelegramHandle extends MainTelegramHandle
{
    public mixed $chatId = null;

    public function __construct($telegramService, $botInfo)
    {
        parent::__construct($telegramService, $botInfo);
        $this->text = $this->telegramService->data['text'] ?? '';

    }

    public function handle(): void
    {
        $this->chatId = $telegramService->from['id'] ?? null;
        if (Str::startsWith($this->text, '/start')) {
            return;
        }
    }
}
