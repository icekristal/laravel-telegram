<?php

namespace Icekristal\LaravelTelegram\Services;

use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Illuminate\Support\Facades\Http;

class IceTelegramService
{
    public mixed $typeInfo = null;
    public mixed $data;
    public array $infoBot;
    public mixed $from;
    public mixed $type;
    public mixed $owner;
    public mixed $callbackQuery = null;


    public function __construct(array $infoBot)
    {
        $this->infoBot = $infoBot;
    }

    public function handle(array $data)
    {
        if (isset($data['message'])) {
            $this->typeInfo = 'message';
        } else if (isset($data['callback_query'])) {
            $this->typeInfo = 'callback_query';
        }

        $this->data = $data['message'] ?? $data['callback_query'] ?? null;
        $this->from = $data['message']['from'] ?? $data['callback_query']['from'] ?? null;
        $this->type = '';

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
            } elseif ($this->typeInfo == 'callback_query') {
                $this->type = 'callback_query';
                $this->callbackQuery = $this->data;
            }

            $this->owner = ServiceTelegram::query()->where('chat_id', $this->from['id'])->first()?->owner ?? null;

            app()->setLocale($data['message']['from']['language_code'] ?? 'ru');
        }
    }


    public function sendMessage(array $params)
    {
        Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendMessage', $params);
    }

    public function sendCallback(array $params)
    {
        Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/answerCallbackQuery', $params);
    }

    public function sendQR(array $params, string $text)
    {
        $params['photo'] = 'https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=' . $text;
        Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendPhoto', $params);
    }

    public function sendPhoto(array $params, string $url)
    {
        $paramsSend['photo'] = $url;
        $paramsSend['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendPhoto', $paramsSend);
    }


    public function sendDocument(array $params, string $filePath)
    {
        $paramsSend['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        $paramsSend['document'] = $filePath;
        Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendDocument', $paramsSend);
    }

    public function getPathFile($fileId): bool|string
    {
        $infoFile = Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/getFile?file_id=' . $fileId);
        if ($infoFile['ok'] && $this->infoBot['is_save_files']) {
            $urlFile = "https://api.telegram.org/file/bot" . $this->infoBot['token'] . "/{$infoFile['result']['file_path']}";
            $ext = explode(".", $urlFile);
            $lastInfo = end($ext);
            $name_our_new_file = "t_" . time() . "." . $lastInfo;
            $fullPath = "{$this->infoBot['path_save_files']}" . $name_our_new_file;
            copy($urlFile, $fullPath);
            return $this->infoBot['path_save_files'] . $name_our_new_file;
        }
        return false;
    }

    /**
     * @param $botToken
     * @return string
     */
    public static function hashBotToken($botToken): string
    {
        return md5($botToken);
    }
}
