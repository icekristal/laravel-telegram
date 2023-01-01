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
        if(is_null($to)) return;

        return IceTelegram::setChatId($to)->setParams([
            'text' => $message->content
        ])->sendMessage();
    }
}
