<?php

namespace Icekristal\LaravelTelegram\Http\Controllers;

use Icekristal\LaravelTelegram\Jobs\IceTelegramJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonException;

class WebhookController extends BaseController
{
    public function index(Request $request): Response
    {
        if (!$this->isDuplicate($request->all())) {
            $nameRoute = $request->route()->getName();
            $botName = trim(Str::of($nameRoute)->after("wh_telegram."));
            $infoBot = config("telegram_service.bots.{$botName}") ?? null;
            if(!is_null($infoBot)) {
                if(!isset($infoBot['is_queue_handler'])) {
                    dispatch(new IceTelegramJob($request->all(), $infoBot))->onQueue($infoBot['queue_webhook'] ?? 'default');
                }else{
                    if($infoBot['is_queue_handler']) {
                        dispatch(new IceTelegramJob($request->all(), $infoBot))->onQueue($infoBot['queue_webhook'] ?? 'default');
                    }else{
                        dispatch_sync(new IceTelegramJob($request->all(), $infoBot));
                    }
                }
            }
        }

        return new Response([true], 200);
    }


    protected function isDuplicate(array $request): bool
    {
        if (!config('telegram_service.is_enable_webhook_cache')) {
            return false;
        }

        try {
            if (!isset($request['callback_query'])) {
                return false;
            }

            $request = Arr::except($request, ['update_id']);

            if (isset($request['callback_query']['id'])) {
                unset($request['callback_query']['id']);
            }

            if (isset($request['callback_query']['message']['date'])) {
                unset($request['callback_query']['message']['date']);
            }

            $hash = hash('xxh128', json_encode($request, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            $key = sprintf('telegram_webhook_%s', $hash);

            if (Cache::has($key)) {
                return true;
            }

            Cache::put($key, true, config('telegram.webhook.ttl'));

            return false;
        } catch (JsonException) {
            return false;
        }
    }
}
