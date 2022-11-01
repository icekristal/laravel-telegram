<?php

namespace Icekristal\LaravelTelegram\Services;

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
            } elseif (isset($this->data['location'])) {
                $this->type = 'location';
            } elseif ($this->typeInfo == 'callback_query') {
                $this->type = 'callback_query';
                $this->callbackQuery = $this->data;
            }

            $this->owner = ServiceTelegram::query()->where('chat_id', $this->from['id'])->first()?->owner ?? null;

            self::saveMessage($this->data, $this->infoBot, $this->owner);

            app()->setLocale($data['message']['from']['language_code'] ?? 'ru');
        }
    }


    public function sendMessage(array $params)
    {
        if (isset($params['text']) && !is_null($params['text']) && $params['text'] != '' && $params['text'] != ' ') {
            $answer = Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendMessage', $params);
            self::saveAnswer($answer, $this->infoBot);
            return $answer;
        }
        return false;

    }

    public function deleteMessage(array $params)
    {
        return Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/deleteMessage', $params);
    }

    public function sendCallback(array $params)
    {
        return Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/answerCallbackQuery', $params);
    }

    public function sendQR(array $params, string $text)
    {
        $params['photo'] = 'https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=' . $text;
        Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendPhoto', $params);
    }

    public function sendLocation(array $params)
    {
        $paramsSend['chat_id'] = $params['chat_id'] ?? $this->from['id'] ?? null;
        $paramsSend['latitude'] = $params['latitude'];
        $paramsSend['longitude'] = $params['longitude'];

        if (isset($params['live_period'])) {
            $paramsSend['live_period'] = $params['live_period'] ?? '';
        }
        if (isset($params['reply_markup'])) {
            $paramsSend['reply_markup'] = json_encode($params['reply_markup']) ?? '';
        }
        if (isset($params['horizontal_accuracy'])) {
            $paramsSend['horizontal_accuracy'] = $params['horizontal_accuracy'] ?? '';
        }
        if (isset($params['proximity_alert_radius'])) {
            $paramsSend['proximity_alert_radius'] = $params['proximity_alert_radius'] ?? '';
        }
        if (isset($params['disable_notification'])) {
            $paramsSend['disable_notification'] = $params['disable_notification'] ?? false;
        }
        if (isset($params['protect_content'])) {
            $paramsSend['protect_content'] = $params['protect_content'] ?? false;
        }
        if (isset($params['reply_to_message_id'])) {
            $paramsSend['reply_to_message_id'] = intval($params['reply_to_message_id']);
        }

        return Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendLocation', $paramsSend);
    }

    public function sendPhoto(array $params, string $url)
    {
        $paramsSend['photo'] = $url;
        $paramsSend['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        if (isset($params['caption'])) {
            $paramsSend['caption'] = $params['caption'] ?? '';
        }
        if (isset($params['file_id'])) {
            $paramsSend['file_id'] = $params['file_id'] ?? '';
        }
        if (isset($params['caption_entities'])) {
            $paramsSend['caption_entities'] = $params['caption_entities'] ?? '';
        }
        if (isset($params['reply_markup'])) {
            $paramsSend['reply_markup'] = json_encode($params['reply_markup']) ?? '';
        }
        if (isset($params['protect_content'])) {
            $paramsSend['protect_content'] = $params['protect_content'] ?? false;
        }
        if (isset($params['reply_to_message_id'])) {
            $paramsSend['reply_to_message_id'] = intval($params['reply_to_message_id']);
        }

        $answer = Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendPhoto', $paramsSend);
        self::saveAnswer($answer, $this->infoBot);
        return $answer;
    }


    public function sendDocument(array $params, string $filePath)
    {
        $paramsSend['chat_id'] = $params['chat_id'] ?? $this->from['id'];
        $paramsSend['document'] = $filePath;
        if (isset($params['caption'])) {
            $paramsSend['caption'] = $params['caption'] ?? '';
        }
        if (isset($params['caption_entities'])) {
            $paramsSend['caption_entities'] = $params['caption_entities'] ?? '';
        }
        if (isset($params['file_id'])) {
            $paramsSend['file_id'] = $params['file_id'] ?? '';
        }
        if (isset($params['reply_markup'])) {
            $paramsSend['reply_markup'] = json_encode($params['reply_markup']) ?? '';
        }
        if (isset($params['protect_content'])) {
            $paramsSend['protect_content'] = $params['protect_content'] ?? false;
        }
        if (isset($params['reply_to_message_id'])) {
            $paramsSend['reply_to_message_id'] = intval($params['reply_to_message_id']);
        }

        $answer = Http::post('https://api.telegram.org/bot' . $this->infoBot['token'] . '/sendDocument', $paramsSend);
        self::saveAnswer($answer, $this->infoBot);
        return $answer;
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

    public static function saveAnswer($answerInfo, $infoBot, $owner = null)
    {
        try {
            if (isset($infoBot['is_save_answer']) && $infoBot['is_save_answer'] && isset($answerInfo['result']['message_id']) && !is_null($answerInfo['result']['message_id']) && !is_null($answerInfo) && $answerInfo['ok']) {

                if (!is_null($owner)) {
                    $owner->ownerTelegramMessages()->create([
                        'message_id' => $answerInfo['result']['message_id'],
                        'bot_key' => IceTelegramService::hashBotToken($infoBot['token']) ?? null,
                        'chat_id' => $answerInfo['result']['chat']['id'],
                        'other_info' => $answerInfo['result'],
                    ]);
                } else {
                    ServiceTelegramOwnerMessage::query()->create([
                        'message_id' => $answerInfo['result']['message_id'],
                        'bot_key' => IceTelegramService::hashBotToken($infoBot['token']) ?? null,
                        'chat_id' => $answerInfo['result']['chat']['id'],
                        'other_info' => $answerInfo['result'],
                    ]);
                }
            }

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public static function saveMessage($data, $infoBot, $owner = null)
    {
        try {
            if (isset($infoBot['is_save_answer']) && $infoBot['is_save_answer'] && isset($data['message_id']) && !is_null($data['message_id'])) {

                if (!is_null($owner) && isset($data['message_id'])) {
                    $owner->ownerTelegramMessages()->create([
                        'message_id' => $data['message_id'],
                        'bot_key' => IceTelegramService::hashBotToken($infoBot['token']) ?? null,
                        'chat_id' => $data['chat']['id'],
                        'other_info' => $data,
                    ]);
                } else {
                    ServiceTelegramOwnerMessage::query()->create([
                        'message_id' => $data['message_id'],
                        'bot_key' => IceTelegramService::hashBotToken($infoBot['token']) ?? null,
                        'chat_id' => $data['chat']['id'],
                        'other_info' => $data,
                    ]);
                }
            }

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
