<?php

namespace Icekristal\LaravelTelegram\Jobs;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IceTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $infoBot = [];
    public $request;
    public string $message;

    /**
     *
     *
     * @return void
     */
    public function __construct($request, $infoBot)
    {
        $this->request = $request;
        $this->infoBot = $infoBot;
    }

    /**
     *
     *
     * @return void
     */
    public function handle(): void
    {
        $telegram = new IceTelegramService($this->infoBot);
        $telegram->handle($this->request);

        if ($this->infoBot['is_save_database'] && isset($telegram->from['id']) && $telegram->from['id'] > 0) {
            ServiceTelegram::query()->updateOrCreate([
                'chat_id' => $telegram->from['id'],
                'bot_key' => IceTelegram::setInfoBot($this->infoBot)->hashBotToken(),
            ], [
                'username' => $telegram->from['username'] ?? null,
                'alias' => $telegram->from['alias'] ?? null,
            ]);
        }

        $checkConfig = $this->infoBot['is_send_answer_group_only_entities_bot'] ?? true;

        if ($telegram->isGroupChat && $telegram->typeInfo != 'message_reaction') {
            if ($checkConfig && !$telegram->isEntitiesBot) {
                return;
            }
        }

        $infoAnswerUser = [];
        if ($this->infoBot['is_technical_job']) {
            $infoAnswerUser['only_message'] = __('telegram_service.technical_work');
        } else {
            $infoAnswerUser = match ($telegram->type) {
                'video' => !is_null($this->infoBot['method_messages']['video']) ? (new $this->infoBot['method_messages']['video']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->data),
                'video_note' => !is_null($this->infoBot['method_messages']['video_note']) ? (new $this->infoBot['method_messages']['video_note']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->data),
                'voice' => !is_null($this->infoBot['method_messages']['voice']) ? (new $this->infoBot['method_messages']['voice']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->data),
                'photo' => !is_null($this->infoBot['method_messages']['photo']) ? (new $this->infoBot['method_messages']['photo']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->data),
                'document' => !is_null($this->infoBot['method_messages']['document']) ? (new $this->infoBot['method_messages']['document']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->data),
                'text' => !is_null($this->infoBot['method_messages']['text']) ? (new $this->infoBot['method_messages']['text']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->data),
                'location' => !is_null($this->infoBot['method_messages']['location']) ? (new $this->infoBot['method_messages']['location']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->data),
                'callback_query' => !is_null($this->infoBot['method_callback_query']) ? (new $this->infoBot['method_callback_query']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->callbackQuery),
                'message_reaction' => !is_null($this->infoBot['method_message_reaction']) ? (new $this->infoBot['method_message_reaction']($telegram, $this->infoBot))->getResult() : $this->defaultAnswer($telegram->messageReaction),
                default => []
            };
        }


        if (isset($infoAnswerUser['only_message'])) {
            $data = [
                'chat_id' => $telegram->from['id'],
                'text' => $infoAnswerUser['only_message'],
            ];
            if(isset($infoAnswerUser['parse_mode']) && !is_null($infoAnswerUser['parse_mode'])) {
                $data['parse_mode'] = $infoAnswerUser['parse_mode'];
            }
            if ($telegram->isEntitiesBot) {
                $data['reply_to_message_id'] = $telegram->messageId;
            }
            $telegram->sendMessage($data);
        }

        if (isset($infoAnswerUser['image'])) {
            $telegram->sendPhoto([
                'caption' => $infoAnswerUser['caption'] ?? null,
            ], $infoAnswerUser['image']);
        }


        if (isset($infoAnswerUser['file'])) {
            $telegram->sendDocument([
                'caption' => $infoAnswerUser['caption'] ?? null,
            ], $infoAnswerUser['file']);
        }

        if (isset($infoAnswerUser['message'])) {
            $data = [
                'chat_id' => $telegram->from['id'],
                'text' => $infoAnswerUser['message'],
            ];

            if ($telegram->isEntitiesBot) {
                $data['reply_to_message_id'] = $telegram->messageId;
            }

            isset($infoAnswerUser['keyboard']) ? $data['reply_markup'] = json_encode($infoAnswerUser['keyboard']) : '';

            if(isset($infoAnswerUser['parse_mode']) && !is_null($infoAnswerUser['parse_mode'])) {
                $data['parse_mode'] = $infoAnswerUser['parse_mode'];
            }
            $telegram->sendMessage($data);
        }



        if ($telegram->type == 'callback_query' && isset($telegram->data['id'])) {
            $paramCallback = [
                'callback_query_id' => $telegram->data['id'],
                'text' => $infoAnswerUser['callback_message'] ?? ' ',
                'show_alert' => boolval($infoAnswerUser['show_alert']) ?? false,
                'chat_id' => $telegram->from['id'],
            ];

            if (isset($infoAnswerUser['url'])) {
                $paramCallback['url'] = (string)$infoAnswerUser['url'];
            }
            if (isset($infoAnswerUser['cache_time'])) {
                $paramCallback['cache_time'] = intval($infoAnswerUser['cache_time']);
            }

            $telegram->sendCallback($paramCallback);
        }
    }

    private function defaultAnswer($data): array
    {
        return [];
    }
}
