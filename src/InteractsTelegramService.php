<?php

namespace Icekristal\LaravelTelegram;

use Icekristal\LaravelTelegram\Jobs\IceTelegramSendMessage;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
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

        dispatch(new IceTelegramSendMessage($this->telegram($botName)?->first()?->chat_id, $message, $replyMarkup, $additionalFile, $botName, $ownerMessage))
            ->onQueue($botInfo['queue_send'] ?? 'default');
    }

    /**
     * Удаляем сообщение
     *
     * @param $messageId
     * @param $botName
     * @return Response
     */
    public function deleteTelegramMessage($messageId, $botName = null)
    {
        if (is_null($botName)) {
            $botInfo = config('telegram_service.bots.' . config('telegram_service.default_bot'));
        } else {
            $botInfo = config('telegram_service.bots.' . $botName);
        }

        return (new IceTelegramService($botInfo))->deleteMessage([
            'message_id' => intval($messageId),
            'chat_id' => $this->telegram($botName)?->first()?->chat_id
        ]);
    }
}
