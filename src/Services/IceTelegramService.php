<?php

namespace Icekristal\LaravelTelegram\Services;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IceTelegramService
{
    public mixed $typeInfo = null;
    public mixed $data = null;
    public array $infoBot;
    public bool $isGroupChat = false;
    public bool $isEntitiesBot = false;
    public mixed $from = null;
    public ?string $type = null;
    public mixed $owner = null;
    public mixed $messageId = null;
    public mixed $callbackQuery = null;
    public mixed $messageReaction = null;

    public function __construct(array $infoBot)
    {
        $this->infoBot = $infoBot;
        $defaultBot = config('telegram_service.default_bot');
        $this->infoBot['main_telegram_server_url'] = rtrim(config("telegram_service.bots.{$defaultBot}.main_telegram_server_url", "https://api.telegram.org"), '/');
    }

    /**
     * @param array $data
     * @return void
     */
    public function handle(array $data): void
    {
        $this->typeInfo = match(true) {
            isset($data['message']) => 'message',
            isset($data['edited_message']) => 'message',
            isset($data['callback_query']) => 'callback_query',
            isset($data['message_reaction']) => 'message_reaction',
            default => null,
        };

        if (!$this->typeInfo) return;

        $key = isset($data['edited_message']) ? 'edited_message' : $this->typeInfo;
        $this->data = $data[$key] ?? null;
        $this->from = $this->data['from'] ?? $data['message_reaction']['user'] ?? null;

        if (empty($this->data) || empty($this->from)) return;

        $this->messageId = $this->data['message_id']
            ?? $this->data['message']['message_id']
            ?? $this->getLastOwnerMessageId($this->from['id']);

        if (!$this->messageId) return;

        $this->processChatData();
        $this->determineMessageType();

        $this->owner = ServiceTelegram::query()->where('chat_id', $this->from['id'])?->first()?->owner ?? null;

        IceTelegram::setInfoBot($this->infoBot)->setOwner($this->owner)->saveAnswer($data);
        app()->setLocale($this->from['language_code'] ?? 'ru');
    }

    /**
     * @return void
     */
    private function processChatData(): void
    {
        if (isset($this->data['chat']) && in_array($this->data['chat']['type'], ['group', 'supergroup'])) {
            $this->isGroupChat = true;
            $this->from['id'] = $this->data['chat']['id'];

            $fullNameBot = "@" . ($this->infoBot['name'] ?? '');
            if (isset($this->data['entities']) && str_contains($this->data['text'] ?? '', $fullNameBot)) {
                $this->isEntitiesBot = true;
            }
        }

        $this->from['username'] ??= "#" . rand(1000, 999999999);
        $this->from['alias'] = str_starts_with($this->from['username'], '#') ? $this->from['username'] : "@{$this->from['username']}";
    }

    /**
     * @return void
     */
    private function determineMessageType(): void
    {
        if ($this->typeInfo === 'callback_query') {
            $this->type = 'callback_query';
            $this->callbackQuery = $this->data;
            return;
        }

        if ($this->typeInfo === 'message_reaction') {
            $this->type = 'message_reaction';
            $this->messageReaction = $this->data;
            return;
        }

        $mediaTypes = ['video', 'video_note', 'voice', 'photo', 'document', 'text', 'location'];
        foreach ($mediaTypes as $mediaType) {
            if (isset($this->data[$mediaType])) {
                $this->type = $mediaType;
                break;
            }
        }
    }

    /**
     * @param int|string $chatId
     * @return int|null
     */
    private function getLastOwnerMessageId(int|string $chatId): ?int
    {
        return ServiceTelegramOwnerMessage::query()
            ->where('chat_id', $chatId)
            ->latest('id')
            ->value('message_id');
    }

    /**
     * @param array $params
     * @return void
     * @throws ConnectionException
     */
    public function sendMessage(array $params): void
    {
        $params['message_id'] = $this->messageId;

        if (!empty($params['is_delete_last_message'])) {
            $this->deleteMessage($params);
        }

        if (!empty($params['is_edit_message']) && $this->messageId) {
            $this->editMessage($params);
            return;
        }

        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendMessage();
    }

    /**
     * @param array $params
     * @return void
     * @throws ConnectionException
     */
    public function editMessage(array $params): void
    {
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->editMessageText();
    }

    /**
     * @param array $params
     * @return void
     * @throws ConnectionException
     */
    public function deleteMessage(array $params): void
    {
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->deleteMessage();
    }

    /**
     * @param array $params
     * @return void
     * @throws ConnectionException
     */
    public function sendCallback(array $params): void
    {
        if (!empty($params['is_edit_message']) && $this->messageId) {
            $this->editMessage($params);
            return;
        }
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendCallback();
    }

    /**
     * @param array $params
     * @param string $text
     * @return void
     * @throws ConnectionException
     */
    public function sendQR(array $params, string $text): void
    {
        $params['text'] = $text;
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendQR();
    }

    /**
     * @param array $params
     * @return void
     * @throws ConnectionException
     */
    public function sendLocation(array $params): void
    {
        $params['chat_id'] ??= $this->from['id'] ?? null;
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendLocation();
    }

    /**
     * @param array $params
     * @param string $url
     * @return void
     * @throws ConnectionException
     */
    public function sendPhoto(array $params, string $url): void
    {
        $params['photo'] = $url;
        $params['chat_id'] ??= $this->from['id'];
        $params['message_id'] = $this->messageId ?? $this->getLastOwnerMessageId($params['chat_id']);

        if (!empty($params['is_edit_message']) && $this->messageId) {
            $this->editMessage($params);
            return;
        }

        if (!empty($params['is_delete_last_message'])) {
            $this->deleteMessage($params);
        }

        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendPhoto();
    }

    /**
     * @param array $params
     * @param string $filePath
     * @return void
     * @throws ConnectionException
     */
    public function sendDocument(array $params, string $filePath): void
    {
        $params['chat_id'] ??= $this->from['id'];
        $params['document'] = $filePath;
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendDocument();
    }


    /**
     * @param $fileId
     * @return bool|string
     */
    public function getPathFile($fileId): bool|string
    {
        if (empty($this->infoBot['is_save_files']) || !$this->infoBot['is_save_files']) {
            return false;
        }

        $response = Http::post("{$this->infoBot['main_telegram_server_url']}/bot{$this->infoBot['token']}/getFile", [
            'file_id' => $fileId
        ]);

        if (!$response->successful() || empty($response->json('ok'))) {
            return false;
        }

        $resultPath = $response->json('result.file_path');
        $urlFile = "{$this->infoBot['main_telegram_server_url']}/file/bot{$this->infoBot['token']}/{$resultPath}";

        $extension = pathinfo($resultPath, PATHINFO_EXTENSION);
        $fileName = "t_" . time() . "_" . Str::random(10) . "." . $extension;

        $directory = trim($this->infoBot['path_save_files'] ?? 'telegram', '/');
        $relativePath = "{$directory}/{$fileName}";

        Storage::disk('local')->makeDirectory($directory);

        $fullPath = Storage::disk('local')->path($relativePath);

        if (!empty($this->infoBot['is_active_log'])) {
            info("WebhookController_Telegram _ FILE: Start downloading", ['url' => $urlFile, 'destination' => $fullPath]);
        }

        try {
            $downloadResponse = Http::timeout(60)->sink($fullPath)->get($urlFile);

            if ($downloadResponse->successful()) {
                return $fullPath;
            }

            return false;
        } catch (\Exception $e) {
            if (!empty($this->infoBot['is_active_log'])) {
                info("WebhookController_Telegram _ FILE: Download failed", ['message' => $e->getMessage()]);
            }

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return false;
        }
    }

    /**
     * @param string $fileId
     * @return string|null
     */
    public function getUrlFile(string $fileId): ?string
    {
        try {
            $response = Http::timeout(10)->get("{$this->infoBot['main_telegram_server_url']}/bot{$this->infoBot['token']}/getFile", [
                'file_id' => $fileId
            ]);

            if ($response->successful() && $response->json('ok')) {
                $filePath = $response->json('result.file_path');
                return "{$this->infoBot['main_telegram_server_url']}/file/bot{$this->infoBot['token']}/{$filePath}";
            }

            if (!empty($this->infoBot['is_active_log'])) {
                info("WebhookController_Telegram _ FILE URL: Telegram API error", ['response' => $response->json()]);
            }

        } catch (\Exception $e) {
            if (!empty($this->infoBot['is_active_log'])) {
                info("WebhookController_Telegram _ FILE URL: Request failed", ['message' => $e->getMessage()]);
            }
        }

        return null;
    }
}
