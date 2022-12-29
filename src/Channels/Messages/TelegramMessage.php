<?php

namespace Icekristal\LaravelTelegram\Channels\Messages;

class TelegramMessage
{
    public $content;

    public function content($content): static
    {
        $this->content = $content;
        return $this;
    }
}
