<?php

namespace Icekristal\LaravelTelegram\Services;

use JetBrains\PhpStorm\ArrayShape;

class MainTelegramHandle
{

    protected IceTelegramService|null $telegramService = null;

    protected array|null $botInfo = null;
    public $onlyMessage = null;
    public $onlyReaction = null;
    public $messageCallback = null;
    public $message = null;
    public $caption = null;
    public $keyboard = null;
    public $image = null;
    public $file = null;
    public $parseMode = null;

    public bool $showAlert = false;
    public $cacheTime = 1;
    public $url = null;
    public bool $isReactiveEditMessage = false;
    public bool $isDeleteLastMessage = false;
    public bool $is_disable_web_page_preview = true;

    public function __construct($telegramService, $botInfo)
    {
        $this->telegramService = $telegramService;
        $this->botInfo = $botInfo;
    }

    public function getResult(): array
    {
        return [
            'only_message' => $this->onlyMessage,
            'only_reaction' => $this->onlyReaction,
            'callback_message' => $this->messageCallback,
            'message' => $this->message,
            'keyboard' => $this->keyboard,
            'image' => $this->image,
            'file' => $this->file,
            'show_alert' => $this->showAlert,
            'cache_time' => $this->cacheTime,
            'url' => $this->url,
            'caption' => $this->caption,
            'parse_mode' => $this->parseMode ?? null,
            'is_reactive_edit_message' => $this->isReactiveEditMessage ?? null,
            'is_delete_last_message' => $this->isDeleteLastMessage ?? null,
            'is_disable_web_page_preview' => $this->is_disable_web_page_preview ?? true,
        ];
    }
}
