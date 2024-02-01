<?php

namespace Icekristal\LaravelTelegram\Channels\Messages;

class TelegramMessage
{
    public $content;

    public $sendedMessageId = null;
    public $saveModelSentedMessage = null;
    public string $fieldSaveModelSentedMessage = 'message_id';

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

    /**
     * @param null $saveModelSentedMessage
     * @param string $fieldSaveModelSentedMessage
     * @return TelegramMessage
     */
    public function setSaveModelSentedMessage($saveModelSentedMessage, string $fieldSaveModelSentedMessage='message_id'): static
    {
        $this->saveModelSentedMessage = $saveModelSentedMessage;
        $this->fieldSaveModelSentedMessage = $fieldSaveModelSentedMessage;
        return $this;
    }
}
