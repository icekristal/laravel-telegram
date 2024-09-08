<?php

namespace Icekristal\LaravelTelegram\Channels;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toTelegram($notifiable);
        $to = $notifiable->routeNotificationFor('Telegram');
        if (is_null($to)) return null;

        $sendInfo = IceTelegram::setChatId($to);
        $sendInfo->setSaveModelSentedMessage($message->saveModelSentedMessage, $message->fieldSaveModelSentedMessage);
        $sendInfo->setParams([
            'text' => $message->content,
            'parse_mode' => 'html',
        ])->sendMessage();
        return $sendInfo;
    }
}
