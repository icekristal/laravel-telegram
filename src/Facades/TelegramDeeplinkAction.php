<?php

namespace Icekristal\LaravelTelegram\Facades;


use Icekristal\LaravelTelegram\Models\Deeplink;
use Icekristal\LaravelTelegram\Services\DeeplinkService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static DeeplinkService setInfoBot(array $infoBot)
 * @method static DeeplinkService for(mixed $owner)
 * @method static string make(string $action, ...$parameters)
 * @method static string static(string $action, ...$parameters)
 * @method static Deeplink find(string $hash)
 */
class TelegramDeeplinkAction extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'high.ice.telegram_deeplink';
    }
}
