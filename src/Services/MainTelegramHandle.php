<?php

namespace Icekristal\LaravelTelegram\Services;

use JetBrains\PhpStorm\ArrayShape;

class MainTelegramHandle
{

    protected IceTelegramService|null $telegramService = null;
    protected array|null $botInfo = null;
    public $onlyMessage = null;
    public $message = null;
    public $keyboard = null;
    public $image = null;
    public $file = null;

    public function __construct($telegramService, $botInfo)
    {
        $this->telegramService = $telegramService;
        $this->botInfo = $botInfo;
    }

    #[ArrayShape(['only_message' => "null", 'message' => "null", 'keyboard' => "null", 'image' => "null", 'file' => "null"])] public function getResult(): array
    {
        return [
            'only_message' => $this->onlyMessage,
            'message' => $this->message,
            'keyboard' => $this->keyboard,
            'image' => $this->image,
            'file' => $this->file,
        ];
    }
}
