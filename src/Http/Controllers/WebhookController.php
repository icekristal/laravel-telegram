<?php

namespace Icekristal\LaravelTelegram\Http\Controllers;

use Icekristal\LaravelTelegram\Jobs\IceTelegramJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonException;

class WebhookController extends BaseController
{
    public function index(Request $request): Response
    {

        $payload = $request->all();

        if (!$this->isDuplicate($payload)) {
            $botName = (string)Str::after($request->route()->getName(), 'wh_telegram.');
            $infoBot = config("telegram_service.bots.{$botName}");

            if ($infoBot) {
                if($request->hasHeader('X-Telegram-Bot-Api-Secret-Token') && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $infoBot['secret_webhook_key']) {
                    return new Response([true], 200);
                }

                if ($infoBot['is_active_log'] ?? false) {
                    info("WebhookController_Telegram", ['all' => $payload]);
                }

                $job = new IceTelegramJob($payload, $infoBot);

                if ($infoBot['is_queue_handler'] ?? true) {
                    dispatch($job)->onQueue($infoBot['queue_webhook'] ?? 'default');
                } else {
                    dispatch_sync($job);
                }
            }
        }

        return new Response([true], 200);
    }

    protected function isDuplicate(array $payload): bool
    {
        if (!config('telegram_service.is_enable_webhook_cache') || !isset($payload['callback_query'])) {
            return false;
        }

        try {
            unset(
                $payload['update_id'],
                $payload['callback_query']['id'],
                $payload['callback_query']['message']['date']
            );

            $hash = hash('xxh128', json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            $key = "telegram_webhook_{$hash}";

            return !Cache::add($key, true, config('telegram_service.webhook_ttl', 30));

        } catch (JsonException) {
            return false;
        }
    }
}
