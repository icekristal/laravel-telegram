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
        $sendInfo->setParams([
            'text' => $message->content
        ])->sendMessage();
        $message->sendedMessageId = $sendInfo->getSendedMessageId() ?? null;
        return $sendInfo;
    }
}
