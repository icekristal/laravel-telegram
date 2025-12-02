<?php

namespace Icekristal\LaravelTelegram\Services;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IceTelegramService
{
    public mixed $typeInfo = null;
    public mixed $data;
    public array $infoBot;
    public bool $isGroupChat = false;
    public bool $isEntitiesBot = false;
    public mixed $from;
    public mixed $type;
    public mixed $owner;
    public mixed $messageId;
    public mixed $callbackQuery = null;
    public mixed $messageReaction = null;


    public function __construct(array $infoBot)
    {
        $this->infoBot = $infoBot;
        if (!isset($infoBot['main_telegram_server_url'])) {
            $this->infoBot['main_telegram_server_url'] = "https://api.telegram.org";
        }
    }

    public function handle(array $data): void
    {
        if (isset($data['message']) || isset($data['edited_message'])) {
            $this->typeInfo = 'message';
        } elseif (isset($data['callback_query'])) {
            $this->typeInfo = 'callback_query';
        } elseif (isset($data['message_reaction'])) {
            $this->typeInfo = 'message_reaction';
        }

        $this->data = $data['message'] ?? $data['callback_query'] ?? $data['message_reaction'] ?? null;
        $this->from = $data['message']['from'] ?? $data['callback_query']['from'] ?? $data['message_reaction']['user'] ?? null;
        $this->type = '';

        try {
            $this->messageId =
                $this?->data['message_id'] ??
                $this?->data['message']['message_id'] ??
                ServiceTelegramOwnerMessage::query()->where('chat_id', $this?->from['id'])?->latest()?->first()?->message_id ??
                null;
        }catch (ConnectionException $e){
            $this->messageId = null;
        }
        if(is_null($this->messageId)) {
            return;
        }

        if (!is_null($this->from)) {
            if (isset($this->data['chat']) && (in_array($this->data['chat']['type'], ['group', 'supergroup']))) {
                $this->isGroupChat = true;
                $this->from['id'] = $this->data['chat']['id'];
                $fullNameBot = "@" . $this->infoBot['name'] ?? null;
                if (isset($this->data['entities']) && str_contains($this?->data['text'], $fullNameBot)) {
                    $this->isEntitiesBot = true;
                }
            }

            if (!isset($this->from['username'])) {
                $num = rand(1000, 999999999);
                $this->from['username'] = "#{$num}";
                $this->from['alias'] = $this->from['username'];
            } else {
                $this->from['alias'] = "@{$this->from['username']}";
            }

            if (isset($this->data['video'])) {
                $this->type = 'video';
            } elseif (isset($this->data['video_note'])) {
                $this->type = 'video_note';
            } elseif (isset($this->data['voice'])) {
                $this->type = 'voice';
            } elseif (isset($this->data['photo'])) {
                $this->type = 'photo';
            } elseif (isset($this->data['document'])) {
                $this->type = 'document';
            } elseif (isset($this->data['text'])) {
                $this->type = 'text';
            } elseif (isset($this->data['location'])) {
                $this->type = 'location';
            } elseif ($this->typeInfo == 'callback_query') {
                $this->type = 'callback_query';
                $this->callbackQuery = $this->data;
            } elseif ($this->typeInfo == 'message_reaction') {
                $this->type = 'message_reaction';
                $this->messageReaction = $this->data;
            }

            $this->owner = ServiceTelegram::query()->where('chat_id', $this->from['id'])->first()?->owner ?? null;

            IceTelegram::setInfoBot($this->infoBot)->setOwner($this->owner)->saveAnswer($data);
            app()->setLocale($data['message']['from']['language_code'] ?? $data['edited_message']['from']['language_code'] ?? 'ru');
        }
    }


    /**
     * @param array $params
     * @return void
     * @throws ConnectionException
     */
    public function sendMessage(array $params): void
    {
        $params['message_id'] = $this->messageId;
        if (isset($params['is_delete_last_message']) && $params['is_delete_last_message']) {
            $this->deleteMessage($params);
        }
        if (isset($params['is_edit_message']) && $params['is_edit_message'] && !is_null($this->messageId)) {
            $this->editMessage($params);
        } else {
            IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendMessage();
        }

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
        if (isset($params['is_edit_message']) && $params['is_edit_message'] && !is_null($this->messageId)) {
            $this->editMessage($params);
        } else {
            IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendCallback();
        }

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

    public function sendLocation(array $params): void
    {
        $paramsSend = $params;
        $paramsSend['chat_id'] = $params['chat_id'] ?? $this->from['id'] ?? null;
        $paramsSend['latitude'] = $params['latitude'];
        $paramsSend['longitude'] = $params['longitude'];
        IceTelegram::setInfoBot($this->infoBot)->setParams($paramsSend)->sendLocation();
    }

    /**
     * @throws ConnectionException
     */
    public function sendPhoto(array $params, string $url): void
    {
        $params['photo'] = $url;
        $params['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        $params['message_id'] = $this->messageId ?? ServiceTelegramOwnerMessage::query()->where('chat_id', $params['chat_id'])->latest()->first()?->message_id ?? null;
        if (isset($params['is_edit_message']) && $params['is_edit_message'] && !is_null($this->messageId)) {
            $this->editMessage($params);
        }else{
            if (isset($params['is_delete_last_message']) && $params['is_delete_last_message']) {
                $this->deleteMessage($params);
            }
            IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendPhoto();
        }

    }


    public function sendDocument(array $params, string $filePath): void
    {
        $params['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        $params['document'] = $filePath;
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendDocument();
    }

    public function getPathFile($fileId): bool|string
    {
        $infoFile = Http::post($this->infoBot['main_telegram_server_url'] . '/bot' . $this->infoBot['token'] . '/getFile?file_id=' . $fileId);
        if ($infoFile['ok'] && $this->infoBot['is_save_files']) {
            $resultPath = $infoFile['result']['file_path'];
            if (!Str::startsWith($resultPath, '/')) {
                $resultPath = "/" . $resultPath;
            }
            $urlFile = $this->infoBot['main_telegram_server_url'] . "/file/bot" . $this->infoBot['token'] . "{$resultPath}";
            $ext = explode(".", $urlFile);
            $lastInfo = end($ext);
            $nameTemp = time() . "_" . rand(100000, 999999);
            $name_our_new_file = "t_" . $nameTemp . "." . $lastInfo;
            $fullPath = "{$this->infoBot['path_save_files']}" . $name_our_new_file;
            copy($urlFile, $fullPath);
            return $this->infoBot['path_save_files'] . $name_our_new_file;
        }
        return false;
    }

    public function getUrlFile($fileId): string|null
    {
        $infoFile = Http::post($this->infoBot['main_telegram_server_url'] . '/bot' . $this->infoBot['token'] . '/getFile?file_id=' . $fileId);
        if ($infoFile['ok'] && isset($infoFile['result']['file_path'])) {
            return $this->infoBot['main_telegram_server_url'] . "/file/bot" . $this->infoBot['token'] . "/{$infoFile['result']['file_path']}";
        }
        return null;
    }
}
