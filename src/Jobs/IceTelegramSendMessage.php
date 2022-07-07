<?php

namespace Icekristal\LaravelTelegram\Jobs;

use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IceTelegramSendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $params;
    public array $additionalFile;
    public array $botInfo;

    /**
     *
     * @return void
     */
    public function __construct($chatId, $message, $replyMarkup = '', $additionalFile = [], $botName = null)
    {
        if (is_null($botName)) {
            $this->botInfo = config('telegram_service.bots.' . config('telegram_service.default_bot'));
        } else {
            $this->botInfo = config('telegram_service.bots.' . $botName);
        }
        $this->additionalFile = $additionalFile;
        $this->params = [
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $replyMarkup != '' ? json_encode($replyMarkup) : ''
        ];
    }

    /**
     *
     * @return void
     */
    public function handle()
    {
        $telegram = new IceTelegramService($this->botInfo);

        if (isset($this->additionalFile['type']) && isset($this->additionalFile['url'])) {
            $paramsForSend['chat_id'] = $this->params['chat_id'];
            if ($this->additionalFile['type'] == 'photo' && $this->additionalFile['url'] != '') {
                $paramsForSend['photo'] = $this->additionalFile['url'];
                $telegram->sendPhoto($paramsForSend, $this->additionalFile['url']);
            }
            if ($this->additionalFile['type'] == 'document' && $this->additionalFile['url'] != '') {
                $paramsForSend['document'] = $this->additionalFile['url'];
                $telegram->sendDocument($paramsForSend, $this->additionalFile['url']);
            }
        }

        $telegram->sendMessage($this->params);
    }
}
