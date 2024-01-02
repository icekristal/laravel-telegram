<?php

namespace Icekristal\LaravelTelegram\Services;

use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HighIceTelegramService
{
    private ?array $infoBot = null;

    private mixed $chatId = null;

    public string $partUrl = '/sendMessage';
    public array $params = [];

    private mixed $owner = null;

    private ?int $sendedMessageId = null;


    /**
     * get info telegram bot
     *
     * @return array
     */
    public function getInfoBot(): array
    {
        return $this->infoBot;
    }

    /**
     * set telegram bot from config
     *
     * @param array $infoBot
     * @return HighIceTelegramService
     */
    public function setInfoBot(array $infoBot): HighIceTelegramService
    {
        $this->infoBot = $infoBot;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChatId(): mixed
    {
        return $this->chatId;
    }

    /**
     * @param mixed $chatId
     * @return HighIceTelegramService
     */
    public function setChatId(mixed $chatId): HighIceTelegramService
    {
        if (is_null($this->infoBot)) {
            $this->setInfoBot(config('telegram_service.bots.' . config('telegram_service.default_bot')));
        }

        $this->chatId = $chatId;
        return $this;
    }


    /**
     * Send only message
     *
     * @return void
     */
    public function sendMessage(): void
    {
        if (!$this->isValidated(['text'])) return;
        $this->partUrl = '/sendMessage';
        $this->sendRequest();
    }

    /**
     * Update text message
     *
     * @return void
     */
    public function editMessageText(): void
    {
        if (!$this->isValidated(['text', 'message_id'])) return;
        $this->partUrl = '/editMessageText';
        $this->sendRequest();
    }

    /**
     * Send delete message
     *
     * @return void
     */
    public function deleteMessage(): void
    {
        if (!$this->isValidated(['message_id'])) return;
        $this->partUrl = '/deleteMessage';
        $this->sendRequest();
    }

    /**
     * send callback
     *
     * @return void
     */
    public function sendCallback(): void
    {
        if (!$this->isValidated(['callback_query_id'])) return;
        $this->partUrl = '/answerCallbackQuery';
        $this->sendRequest();
    }

    /**
     * send photo
     *
     * params:
     *  caption (String) -
     *  parse_mode -
     *  caption_entities -
     *  disable_notification (Boolean) -
     *  protect_content (Boolean) -
     *  reply_to_message_id (Integer) -
     *  reply_markup -
     *
     * @return void
     */
    public function sendPhoto(): void
    {
        if (!$this->isValidated(['photo'])) return;

        $this->partUrl = '/sendPhoto';
        $this->sendRequest();
    }

    /**
     * send document
     *
     * params:
     *  document*
     *  caption (String) -
     *  parse_mode -
     *  caption_entities -
     *  disable_notification (Boolean) -
     *  protect_content (Boolean) -
     *  reply_to_message_id (Integer) -
     *  reply_markup -
     *
     * @return void
     */
    public function sendDocument(): void
    {
        if (!$this->isValidated(['document'])) return;

        $this->partUrl = '/sendDocument';
        $this->sendRequest();
    }

    /**
     * Send location
     *
     * params:
     * latitude*
     * longitude*
     *
     * @return void
     */
    public function sendLocation(): void
    {
        if (!$this->isValidated(['latitude', 'longitude'])) return;
        $this->partUrl = '/sendLocation';
        $this->sendRequest();
    }

    /**
     * Send QR
     *
     * @return void
     */
    public function sendQR(): void
    {
        if (!$this->isValidated(['text'])) return;
        $this->params['photo'] = 'https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=' . $this->params['text'];
        $this->partUrl = '/sendPhoto';
        $this->sendRequest();
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return HighIceTelegramService
     */
    public function setParams(array $params): HighIceTelegramService
    {
        $params['chat_id'] = $this->chatId ?? $params['chat_id'] ?? null;
        $this->params = $params;
        return $this;
    }


    /**
     * Send telegram api request
     *
     * @return void
     */
    private function sendRequest(): void
    {
        if (!isset($this->params['chat_id'])) return;
        $this->saveAnswer(Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . $this->partUrl, $this->params));
    }


    /**
     * @param $arrayRequiredParams
     * @return bool
     */
    private function isValidated($arrayRequiredParams): bool
    {
        foreach ($arrayRequiredParams as $param) {
            if (!isset($this->params[$param])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $answer
     * @return void
     */
    public function saveAnswer($answer): void
    {
        if (!is_null($answer) && isset($answer['result']['message_id'])) {
            $this->sendedMessageId = intval($answer['result']['message_id']) ?? null;
        }

        try {
            if (!(isset($this->infoBot['is_save_answer']) && $this->infoBot['is_save_answer'] && isset($answer['result']['message_id']) && !is_null($answer) && isset($answer['ok']))) {
                return;
            }

            if (!is_null($this->owner)) {
                $this->owner->ownerTelegramMessages()->create([
                    'message_id' => $answer['result']['message_id'],
                    'bot_key' => $this->hashBotToken() ?? null,
                    'chat_id' => $answer['result']['chat']['id'],
                    'other_info' => $answer['result'],
                ]);
            } else {
                ServiceTelegramOwnerMessage::query()->create([
                    'message_id' => $answer['result']['message_id'],
                    'bot_key' => $this->hashBotToken() ?? null,
                    'chat_id' => $answer['result']['chat']['id'],
                    'other_info' => $answer['result'],
                ]);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getOwner(): mixed
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     * @return HighIceTelegramService
     */
    public function setOwner(mixed $owner = null): HighIceTelegramService
    {
        $this->owner = $owner;
        return $this;
    }


    /**
     * Получаем хеш токена
     *
     * @return string
     */
    public function hashBotToken(): string
    {
        return md5($this->infoBot['token']);
    }

    /**
     * Получаем ID отправленного сообщения
     * @return int|null
     */
    public function getSendedMessageId(): ?int
    {
        return $this->sendedMessageId;
    }
}
