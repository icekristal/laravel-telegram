<?php

namespace Icekristal\LaravelTelegram\Facades;


use Icekristal\LaravelTelegram\Services\HighIceTelegramService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static HighIceTelegramService setInfoBot(array $infoBot)
 * @method static HighIceTelegramService setChatId(mixed $chatId)
 * @method static HighIceTelegramService setParams(array $params)
 * @method static HighIceTelegramService setOwner(mixed $owner)
 * @method static HighIceTelegramService editMessageText()
 * @method static HighIceTelegramService sendMessage()
 * @method static HighIceTelegramService deleteMessage()
 * @method static HighIceTelegramService sendCallback()
 * @method static HighIceTelegramService sendPhoto()
 * @method static HighIceTelegramService sendDocument()
 * @method static HighIceTelegramService sendLocation()
 * @method static HighIceTelegramService sendQR()
 * @method static HighIceTelegramService getSendedMessageId()
 * @method static HighIceTelegramService hashBotToken()
 */
class IceTelegram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'high.ice.telegram';
    }
}
