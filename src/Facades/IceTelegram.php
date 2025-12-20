<?php

namespace Icekristal\LaravelTelegram\Facades;


use Icekristal\LaravelTelegram\Services\HighIceTelegramService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Facade;

/**
 * @method static HighIceTelegramService setInfoBot(array $infoBot)
 * @method static HighIceTelegramService setChatId(mixed $chatId)
 * @method static HighIceTelegramService setParams(array $params)
 * @method static HighIceTelegramService setOwner(mixed $owner)
 * @method static void editMessageText()
 * @method static void sendMessage()
 * @method static void deleteMessage()
 * @method static void deleteLastMessage()
 * @method static void sendCallback()
 * @method static void sendPhoto()
 * @method static void sendDocument()
 * @method static void sendLocation()
 * @method static void sendQR()
 * @method static int|null getSendedMessageId()
 * @method static string hashBotToken()
 * @method static string getUrlFile()
 * @method static array|null getResponse()
 */
class IceTelegram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'high.ice.telegram';
    }


    /**
     * @throws BindingResolutionException
     */
    protected static function resolveFacadeInstance($name)
    {
        return app()->make($name);
    }
}
