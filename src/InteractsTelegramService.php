<?php

namespace Icekristal\LaravelTelegram;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Icekristal\LaravelTelegram\Jobs\IceTelegramSendMessage;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Icekristal\LaravelTelegram\Services\HighIceTelegramService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait InteractsTelegramService
{
    /**
     * Для нотификаций по чат ID телеги
     * @return mixed
     */
    public function routeNotificationForTelegram(): mixed
    {
        return $this?->telegram?->chat_id;
    }

    /**
     *
     * @param null $botName
     * @return MorphOne
     */
    public function telegram($botName = null): MorphOne
    {
        if (is_null($botName)) {
            $botName = config('telegram_service.default_bot');
            $infoBot = config("telegram_service.bots.{$botName}");
        } else {
            $infoBot = config("telegram_service.bots.{$botName}");
        }
        $botKey = IceTelegram::setInfoBot($infoBot)->hashBotToken();

        return $this->setConnection($infoBot['db_connection'] ?? env('DB_CONNECTION'))->morphOne(ServiceTelegram::class, 'owner')->where('bot_key', $botKey);
    }

    /**
     *
     * @param null $botName
     * @return MorphMany
     */
    public function telegrams($botName = null): MorphMany
    {
        if (is_null($botName)) {
            $botName = config('telegram_service.default_bot');
            $infoBot = config("telegram_service.bots.{$botName}");
        } else {
            $infoBot = config("telegram_service.bots.{$botName}");
        }

        return $this->setConnection($infoBot['db_connection'] ?? env('DB_CONNECTION'))->morphMany(ServiceTelegram::class, 'owner');
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
            $ownerMessage = $this ?? null;
        }

        $infoTelegram = $this->telegram($botName)?->first();
        if (!is_null($infoTelegram)) {
            dispatch(new IceTelegramSendMessage($infoTelegram?->chat_id, $message, $replyMarkup, $additionalFile, $botName, $ownerMessage))
                ->onQueue($botInfo['queue_send'] ?? 'default');
        }
    }

    /**
     * Удаляем сообщение
     *
     * @param $messageId
     * @param null $chatId
     * @param null $botName
     * @return void
     */
    public function deleteTelegramMessage($messageId, $chatId = null, $botName = null): void
    {
        if (is_null($botName)) {
            $botInfo = config('telegram_service.bots.' . config('telegram_service.default_bot'));
        } else {
            $botInfo = config("telegram_service.bots.{$botName}");
        }
        $chatId = is_null($chatId) ? $this->telegram($botName)?->first()?->chat_id : $chatId;
        IceTelegram::setInfoBot($botInfo)->setChatId($chatId)->setParams([
            'message_id' => intval($messageId),
        ])->deleteMessage();
    }

    /**
     * Редактируем сообщение
     *
     * @param $messageId
     * @param $newText
     * @param null $chatId
     * @param null $botName
     * @return void
     */
    public function editTelegramTextMessage($messageId, $newText, $chatId = null, $botName = null): void
    {
        if (is_null($botName)) {
            $botInfo = config('telegram_service.bots.' . config('telegram_service.default_bot'));
        } else {
            $botInfo = config("telegram_service.bots.{$botName}");
        }
        $chatId = is_null($chatId) ? $this->telegram($botName)?->first()?->chat_id : $chatId;
        IceTelegram::setInfoBot($botInfo)->setChatId($chatId)->setParams([
            'message_id' => intval($messageId),
            'text' => $newText
        ])->editMessageText();
    }

    /**
     *
     * @param null $botName
     * @return MorphMany
     */
    public function ownerTelegramMessages($botName = null): MorphMany
    {
        if (is_null($botName)) {
            $infoBot = config("telegram_service.bots." . config('telegram_service.default_bot'));
        } else {
            $infoBot = config("telegram_service.bots.{$botName}");
        }
        $botKey = IceTelegram::setInfoBot($infoBot)->hashBotToken();
        return $this->setConnection($infoBot['db_connection'] ?? env('DB_CONNECTION'))->morphMany(ServiceTelegramOwnerMessage::class, 'owner')->where('bot_key', $botKey);
    }
}
