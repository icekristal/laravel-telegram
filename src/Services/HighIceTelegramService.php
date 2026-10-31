<?php

namespace Icekristal\LaravelTelegram\Services;

use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class HighIceTelegramService
{
    private array $infoBot = [];
    private mixed $chatId = null;
    public string $partUrl = '/sendMessage';
    public array $params = [];
    private mixed $owner = null;
    private ?int $sendedMessageId = null;
    private mixed $saveModelSentedMessage = null;
    private string $fieldSaveModelSentedMessage = 'message_id';
    public mixed $response = null;

    /**
     *
     */
    public function __construct()
    {
        $defaultBot = config('telegram_service.default_bot');
        $this->infoBot = [
            'main_telegram_server_url' => rtrim(config("telegram_service.bots.{$defaultBot}.main_telegram_server_url", "https://api.telegram.org"), '/'),
            'token' => config("telegram_service.bots.{$defaultBot}.token"),
            'proxy_url' => config("telegram_service.bots.{$defaultBot}.proxy_url"),
        ];
    }

    /**
     * @return array
     */
    public function getInfoBot(): array
    {
        return $this->infoBot;
    }

    /**
     * @param array $infoBot
     * @return $this
     */
    public function setInfoBot(array $infoBot): self
    {
        $this->infoBot = $infoBot;
        $this->infoBot['main_telegram_server_url'] = rtrim($infoBot['main_telegram_server_url'] ?? "https://api.telegram.org", '/');

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
     * @return $this
     */
    public function setChatId(mixed $chatId): self
    {
        if (empty($this->infoBot['token'])) {
            $this->setInfoBot(config('telegram_service.bots.' . config('telegram_service.default_bot')));
        }

        $this->chatId = $chatId;
        return $this;
    }

    /**
     * @return void
     */
    public function sendMessage(): void
    {
        if (!$this->isValidated(['text'])) return;
        if(is_null($this->params['text'])) return;
        $this->partUrl = '/sendMessage';
        $this->sendRequest();
    }

    /**
     * @return void
     */
    public function editMessageText(): void
    {
        if (!$this->isValidated(['text', 'message_id'])) return;
        if(is_null($this->params['text'])) return;
        $this->partUrl = '/editMessageText';
        $this->sendRequest();
    }

    /**
     * @return void
     */
    public function deleteMessage(): void
    {
        if (!$this->isValidated(['message_id'])) return;
        $this->partUrl = '/deleteMessage';
        $this->sendRequest();
    }

    /**
     * @return void
     */
    public function deleteLastMessage(): void
    {
        $chatId = $this->params['chat_id'] ?? $this->chatId ?? null;
        if (!$chatId) return;

        $lastMessageId = ServiceTelegramOwnerMessage::query()
            ->where('chat_id', $chatId)
            ->latest('id')
            ->value('message_id');

        if ($lastMessageId) {
            $this->params['message_id'] = $lastMessageId;
            $this->partUrl = '/deleteMessage';
            $this->sendRequest();
        }
    }

    /**
     * @return void
     */
    public function sendCallback(): void
    {
        if (!$this->isValidated(['callback_query_id'])) return;
        $this->partUrl = '/answerCallbackQuery';
        $this->sendRequest();
    }

    /**
     * @return void
     */
    public function sendPhoto(): void
    {
        if (!$this->isValidated(['photo'])) return;
        $this->partUrl = '/sendPhoto';
        $this->sendRequest();
    }

    /**
     * @return void
     */
    public function sendDocument(): void
    {
        if (!$this->isValidated(['document'])) return;
        $this->partUrl = '/sendDocument';
        $this->sendRequest();
    }

    /**
     * @return void
     */
    public function sendLocation(): void
    {
        if (!$this->isValidated(['latitude', 'longitude'])) return;
        $this->partUrl = '/sendLocation';
        $this->sendRequest();
    }

    /**
     * @return void
     */
    public function sendQR(): void
    {
        if (!$this->isValidated(['text'])) return;

        $sizeQr = $this->params['size_qr'] ?? '250x250';
        $this->params['photo'] = "https://api.qrserver.com/v1/create-qr-code/?size={$sizeQr}&data=" . urlencode($this->params['text']);
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
     * @return $this
     */
    public function setParams(array $params): self
    {
        $params['chat_id'] = $this->chatId ?? $params['chat_id'] ?? null;
        $this->params = $params;
        return $this;
    }

    /**
     * @return void
     */
    private function sendRequest(): void
    {
        if (empty($this->params['chat_id'])) return;

        $client = Http::timeout($this->infoBot['timeout'] ?? 15)->connectTimeout($this->infoBot['connect_timeout'] ?? 30);

        if (!empty($this->infoBot['proxy_url'])) {
            $client->withOptions(['proxy' => $this->infoBot['proxy_url']]);
        }

        $url = "{$this->infoBot['main_telegram_server_url']}/bot{$this->infoBot['token']}{$this->partUrl}";

        try {
            $this->response = $client->post($url, $this->params);

            $this->partUrl = '';

            $this->saveAnswer($this->response->json());
        } catch (\Exception $e) {
            Log::error("Telegram SendRequest Error: " . $e->getMessage());
        }
    }

    /**
     * @return string|null
     */
    public function getUrlFile(): ?string
    {
        if (!$this->isValidated(['file_id'])) return null;

        $url = "{$this->infoBot['main_telegram_server_url']}/bot{$this->infoBot['token']}/getFile";
        $response = Http::get($url, ['file_id' => $this->params['file_id']]);

        if ($response->successful() && $response->json('ok')) {
            $filePath = $response->json('result.file_path');
            return "{$this->infoBot['main_telegram_server_url']}/file/bot{$this->infoBot['token']}/{$filePath}";
        }

        return null;
    }

    /**
     * @param array $requiredParams
     * @return bool
     */
    private function isValidated(array $requiredParams): bool
    {
        return Arr::has($this->params, $requiredParams);
    }

    /**
     * @param array|null $answer
     * @return void
     */
    public function saveAnswer(?array $answer): void
    {
        if (empty($answer['result']['message_id'])) {
            return;
        }

        $this->sendedMessageId = (int) $answer['result']['message_id'];

        if ($this->saveModelSentedMessage) {
            try {
                $this->saveModelSentedMessage->update([
                    $this->fieldSaveModelSentedMessage => $this->sendedMessageId
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to save Sent Message ID: " . $e->getMessage());
            }
        }

        if (empty($this->infoBot['is_save_answer']) || empty($answer['ok'])) {
            return;
        }

        $payload = [
            'message_id' => $this->sendedMessageId,
            'bot_key' => $this->hashBotToken(),
            'chat_id' => $answer['result']['chat']['id'],
            'other_info' => $answer['result'],
        ];

        try {
            if ($this->owner) {
                $this->owner->ownerTelegramMessages()->create($payload);
            } else {
                ServiceTelegramOwnerMessage::query()->create($payload);
            }
        } catch (\Exception $e) {
            Log::error("Failed to save Answer to OwnerMessage: " . $e->getMessage());
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
     * @param mixed|null $owner
     * @return $this
     */
    public function setOwner(mixed $owner = null): self
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return string
     */
    public function hashBotToken(): string
    {
        if (!empty($this->infoBot['is_new_save_key_bot'])) {
            return $this->infoBot['name_bot'];
        }
        return md5($this->infoBot['token']);
    }

    /**
     * @return int|null
     */
    public function getSendedMessageId(): ?int
    {
        return $this->sendedMessageId;
    }

    /**
     * @param $saveModelSentedMessage
     * @param string $fieldSaveModelSentedMessage
     * @return $this
     */
    public function setSaveModelSentedMessage($saveModelSentedMessage, string $fieldSaveModelSentedMessage = 'message_id'): self
    {
        $this->fieldSaveModelSentedMessage = $fieldSaveModelSentedMessage;
        $this->saveModelSentedMessage = $saveModelSentedMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponse(): mixed
    {
        return $this->response;
    }
}
