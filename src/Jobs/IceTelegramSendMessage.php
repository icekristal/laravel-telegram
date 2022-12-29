<?php

namespace Icekristal\LaravelTelegram\Jobs;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegramOwnerMessage;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IceTelegramSendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $params;
    public array $additionalFile;
    public array $botInfo;
    public $ownerAnswer = null;

    /**
     *
     * @return void
     */
    public function __construct($chatId, $message, $replyMarkup = '', $additionalFile = [], $botName = null, $ownerAnswer = null)
    {
        if (is_null($botName)) {
            $this->botInfo = config('telegram_service.bots.' . config('telegram_service.default_bot'));
        } else {
            $this->botInfo = config('telegram_service.bots.' . $botName);
        }
        $this->additionalFile = $additionalFile;
        $this->ownerAnswer = !is_null($ownerAnswer) ? get_class($ownerAnswer)::query()->find($ownerAnswer->id) : null;
        $this->params = [
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $replyMarkup != '' ? json_encode($replyMarkup) : ''
        ];

        if (isset($additionalFile['reply_to_message_id']) && !is_null($additionalFile['reply_to_message_id'])) {
            $this->params['reply_to_message_id'] = $additionalFile['reply_to_message_id'];
        }
    }

    /**
     *
     * @return void
     */
    public function handle(): void
    {
        $paramsForSend = $this->additionalFile;
        if (isset($this->additionalFile['type'])) {
            $paramsForSend['chat_id'] = $this->params['chat_id'];

            if(isset($this->additionalFile['url'])) {
                if ($this->additionalFile['type'] == 'photo' && $this->additionalFile['url'] != '') {
                    $paramsForSend['photo'] = $this->additionalFile['url'];
                    IceTelegram::setInfoBot($this->botInfo)->setParams($paramsForSend)->setOwner($this->ownerAnswer)->sendPhoto();
                }
                if ($this->additionalFile['type'] == 'document' && $this->additionalFile['url'] != '') {
                    $paramsForSend['document'] = $this->additionalFile['url'];
                    IceTelegram::setInfoBot($this->botInfo)->setParams($paramsForSend)->setOwner($this->ownerAnswer)->sendDocument();
                }
            }

            if($this->additionalFile['type'] == 'location' && isset($this->additionalFile['latitude']) && isset($this->additionalFile['longitude'])) {
                IceTelegram::setInfoBot($this->botInfo)->setParams($paramsForSend)->setOwner($this->ownerAnswer)->sendLocation();
            }
        }
        IceTelegram::setInfoBot($this->botInfo)->setParams($this->params)->setOwner($this->ownerAnswer)->sendMessage();
    }
}
