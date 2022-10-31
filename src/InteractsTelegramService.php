<?php

namespace Icekristal\LaravelTelegram;

use Icekristal\LaravelTelegram\Jobs\IceTelegramSendMessage;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Client\Response;

trait InteractsTelegramService
{
    /**
     *
     * @param null $botName
     * @return MorphOne
     */
    public function telegram($botName = null): MorphOne
    {
        if (is_null($botName)) {
            $botKey = IceTelegramService::hashBotToken(config('telegram_service.bots.' . config('telegram_service.default_bot') . '.token'));
        } else {
            $botKey = IceTelegramService::hashBotToken(config('telegram_service.bots.' . $botName . '.token'));
        }

        return $this->morphOne(ServiceTelegram::class, 'owner')->where('bot_key', $botKey);
    }

    /**
     *
     * @return MorphMany
     */
    public function telegrams(): MorphMany
    {
        return $this->morphMany(ServiceTelegram::class, 'owner');
    }

    /**
     * @return MorphTo
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param $message
     * @param string $replyMarkup
     * @param array $additionalFile
     * @param null $botName
     * @param null $ownerMessage
     * @return void
     */
    public function sendTelegramMessage($message, $replyMarkup = '', array $additionalFile = [], $botName = null, $ownerMessage = null): void
    {
        if (is_null($botName)) {
            $botInfo = config('telegram_service.bots.' . config('telegram_service.default_bot'));
        } else {
            $botInfo = config('telegram_service.bots.' . $botName);
        }

        if (is_null($ownerMessage)) {
            $ownerMessage = $this->owner() ?? null;
        }

        dispatch(new IceTelegramSendMessage($this->telegram($botName)?->first()?->chat_id, $message, $replyMarkup, $additionalFile, $botName, $ownerMessage))
            ->onQueue($botInfo['queue_send'] ?? 'default');
    }

    /**
     * Удаляем сообщение
     *
     * @param $messageId
     * @param null $chatId
     * @param null $botName
     * @return Response
     */
    public function deleteTelegramMessage($messageId, $chatId = null ,$botName = null)
    {
        if (is_null($botName)) {
            $botInfo = config('telegram_service.bots.' . config('telegram_service.default_bot'));
        } else {
            $botInfo = config('telegram_service.bots.' . $botName);
        }

        return (new IceTelegramService($botInfo))->deleteMessage([
            'message_id' => intval($messageId),
            'chat_id' => is_null($chatId) ? $this->telegram($botName)?->first()?->chat_id : $chatId
        ]);
    }

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
