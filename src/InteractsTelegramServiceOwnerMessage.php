<?php

namespace Icekristal\LaravelTelegram;

use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsTelegramServiceOwnerMessage
{
    /**
     *
     * @param null $botName
     * @return MorphMany
     */
    public function ownerTelegramMessages($botName = null): MorphMany
    {
        if (is_null($botName)) {
            $botKey = IceTelegramService::hashBotToken(config('telegram_service.bots.' . config('telegram_service.default_bot') . '.token'));
        } else {
            $botKey = IceTelegramService::hashBotToken(config('telegram_service.bots.' . $botName . '.token'));
        }

        return $this->morphMany(ServiceTelegramOwnerMessage::class, 'owner')->where('bot_key', $botKey);
    }
}
