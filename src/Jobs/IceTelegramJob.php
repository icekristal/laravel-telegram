<?php

namespace Icekristal\LaravelTelegram\Jobs;

use Icekristal\LaravelTelegram\Facades\IceTelegram;
use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Icekristal\LaravelTelegram\Services\IceTelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class IceTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $infoBot = [];
    public array $request;
    public string $message = '';

    public function __construct(array $request, array $infoBot)
    {
        $this->request = $request;
        $this->infoBot = $infoBot;
    }

    /**
     * @return void
     * @throws ConnectionException
     * @throws JsonException
     */
    public function handle(): void
    {
        $telegram = new IceTelegramService($this->infoBot);
        $telegram->handle($this->request);

        // Если не удалось определить пользователя или чат - прекращаем обработку
        if (empty($telegram->from['id'])) {
            return;
        }

        $this->saveUserToDatabase($telegram);

        if ($this->shouldIgnoreGroupMessage($telegram)) {
            return;
        }

        $infoAnswerUser = $this->getAnswerInfo($telegram);

        $this->sendResponses($telegram, $infoAnswerUser);
    }

    /**
     * Сохраняет или обновляет пользователя в БД, если это разрешено конфигурацией
     */
    private function saveUserToDatabase(IceTelegramService $telegram): void
    {
        if (empty($this->infoBot['is_save_database']) || $telegram->from['id'] <= 0) {
            return;
        }

        ServiceTelegram::query()->updateOrCreate(
            [
                'chat_id' => $telegram->from['id'],
                'bot_key' => IceTelegram::setInfoBot($this->infoBot)->hashBotToken(),
            ],
            [
                'username' => $telegram->from['username'] ?? null,
                'alias' => $telegram->from['alias'] ?? null,
            ]
        );
    }

    /**
     * Проверяет, нужно ли проигнорировать сообщение в групповом чате
     */
    private function shouldIgnoreGroupMessage(IceTelegramService $telegram): bool
    {
        if (!$telegram->isGroupChat || $telegram->typeInfo === 'message_reaction') {
            return false;
        }

        $checkConfig = $this->infoBot['is_send_answer_group_only_entities_bot'] ?? true;

        return $checkConfig && !$telegram->isEntitiesBot;
    }

    /**
     * Определяет ответ пользователю на основе типа входящего события
     */
    private function getAnswerInfo(IceTelegramService $telegram): array
    {
        if (!empty($this->infoBot['is_technical_job'])) {
            return ['only_message' => __('telegram_service.technical_work')];
        }

        if (empty($telegram->type)) {
            return [];
        }

        $methodKey = in_array($telegram->type, ['callback_query', 'message_reaction'], true)
            ? "method_{$telegram->type}"
            : 'method_messages';

        $handlerClass = is_array($this->infoBot[$methodKey] ?? null)
            ? ($this->infoBot[$methodKey][$telegram->type] ?? null)
            : ($this->infoBot[$methodKey] ?? null);

        $payload = match ($telegram->type) {
            'callback_query' => $telegram->callbackQuery,
            'message_reaction' => $telegram->messageReaction,
            default => $telegram->data,
        };

        if ($handlerClass && class_exists($handlerClass)) {
            return (new $handlerClass($telegram, $this->infoBot))->getResult();
        }

        return $this->defaultAnswer($payload);
    }

    /**
     * Формирует базовый набор параметров для отправки сообщений, исключая дублирование
     * @throws JsonException
     */
    private function buildBaseSendData(IceTelegramService $telegram, array $infoAnswerUser): array
    {
        $data = [
            'chat_id' => $telegram->from['id'],
            'is_edit_message' => $infoAnswerUser['is_reactive_edit_message'] ?? false,
            'is_delete_last_message' => $infoAnswerUser['is_delete_last_message'] ?? false,
            'disable_web_page_preview' => $infoAnswerUser['is_disable_web_page_preview'] ?? true,
        ];

        if (!empty($infoAnswerUser['parse_mode'])) {
            $data['parse_mode'] = $infoAnswerUser['parse_mode'];
        }

        if (isset($infoAnswerUser['keyboard'])) {
            $data['reply_markup'] = json_encode($infoAnswerUser['keyboard'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }


    /**
     * @param IceTelegramService $telegram
     * @param array $infoAnswerUser
     * @return void
     * @throws ConnectionException
     * @throws JsonException
     */
    private function sendResponses(IceTelegramService $telegram, array $infoAnswerUser): void
    {
        if (empty($infoAnswerUser)) {
            return;
        }

        $baseData = $this->buildBaseSendData($telegram, $infoAnswerUser);

        if (isset($infoAnswerUser['only_message'])) {
            $data = $baseData;
            $data['text'] = $infoAnswerUser['only_message'];

            if ($telegram->isEntitiesBot) {
                $data['reply_to_message_id'] = $telegram->messageId;
            }

            $telegram->sendMessage($data);
        }

        if (isset($infoAnswerUser['image'])) {
            $data = $baseData;
            $data['caption'] = $infoAnswerUser['caption'] ?? null;
            $telegram->sendPhoto($data, $infoAnswerUser['image']);
        }

        if (isset($infoAnswerUser['file'])) {
            $telegram->sendDocument([
                'caption' => $infoAnswerUser['caption'] ?? null,
            ], $infoAnswerUser['file']);
        }

        if (isset($infoAnswerUser['message'])) {
            $data = $baseData;
            $data['text'] = $infoAnswerUser['message'];

            if ($telegram->isEntitiesBot) {
                $data['reply_to_message_id'] = $telegram->messageId;
            }

            $telegram->sendMessage($data);
        }

        if ($telegram->type === 'callback_query' && !empty($telegram->data['id'])) {
            $paramCallback = array_merge($baseData, [
                'callback_query_id' => $telegram->data['id'],
                'text' => $infoAnswerUser['callback_message'] ?? ' ',
                'show_alert' => (bool)($infoAnswerUser['show_alert'] ?? false),
            ]);

            if (isset($infoAnswerUser['url'])) {
                $paramCallback['url'] = (string)$infoAnswerUser['url'];
            }

            if (isset($infoAnswerUser['cache_time'])) {
                $paramCallback['cache_time'] = (int)$infoAnswerUser['cache_time'];
            }

            $telegram->sendCallback($paramCallback);
        }
    }

    /**
     * @param mixed $data
     * @return array
     */
    private function defaultAnswer(mixed $data): array
    {
        return [];
    }
}
