<?php

namespace Icekristal\LaravelTelegram\Services;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IceTelegramService
{
    public mixed $typeInfo = null;
    public mixed $data;
    public array $infoBot;
    public mixed $from;
    public mixed $type;
    public mixed $owner;
    public mixed $messageId;
    public mixed $callbackQuery = null;


    public function __construct(array $infoBot)
    {
        $this->infoBot = $infoBot;
    }

    public function handle(array $data): void
    {
        if (isset($data['message'])) {
            $this->typeInfo = 'message';
        } else if (isset($data['callback_query'])) {
            $this->typeInfo = 'callback_query';
        }

        $this->data = $data['message'] ?? $data['callback_query'] ?? null;
        $this->from = $data['message']['from'] ?? $data['callback_query']['from'] ?? null;
        $this->type = '';
        $this->messageId = $this?->data['message']['message_id'] ?? null;

        if (!is_null($this->from)) {
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
            }

            $this->owner = ServiceTelegram::query()->where('chat_id', $this->from['id'])->first()?->owner ?? null;

            IceTelegram::setInfoBot($this->infoBot)->setOwner($this->owner)->saveAnswer($data);
            app()->setLocale($data['message']['from']['language_code'] ?? 'ru');
        }
    }


    public function sendMessage(array $params): void
    {
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendMessage();
    }

    public function deleteMessage(array $params): void
    {
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->deleteMessage();
    }

    public function sendCallback(array $params): void
    {
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendCallback();
    }

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

    public function sendPhoto(array $params, string $url): void
    {
        $params['photo'] = $url;
        $params['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendPhoto();
    }


    public function sendDocument(array $params, string $filePath): void
    {
        $params['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        $params['document'] = $filePath;
        IceTelegram::setInfoBot($this->infoBot)->setParams($params)->sendDocument();
    }

    public function getPathFile($fileId): bool|string
    {
        $infoFile = Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/getFile?file_id=' . $fileId);
        if ($infoFile['ok'] && $this->infoBot['is_save_files']) {
            $urlFile = "https://api.telegram.org/file/bot" . $this->infoBot['token'] . "/{$infoFile['result']['file_path']}";
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
}
