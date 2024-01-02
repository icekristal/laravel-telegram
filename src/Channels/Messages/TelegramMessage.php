<?php

namespace Icekristal\LaravelTelegram\Channels\Messages;

class TelegramMessage
{
    public $content;

    public $sendedMessageId = null;

    public function content($content): static
    {
        $this->content = $content;
        return $this;
    }

    public function sendedMessageId($sendedMessageId): static
    {
        $this->sendedMessageId = $sendedMessageId;
        return $this;
    }
}
