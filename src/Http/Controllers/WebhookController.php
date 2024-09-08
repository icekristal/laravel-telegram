<?php

namespace Icekristal\LaravelTelegram\Http\Controllers;

use Icekristal\LaravelTelegram\Jobs\IceTelegramJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class WebhookController extends BaseController
{
    public function index(Request $request): Response
    {
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
                    dispatch_sync(new IceTelegramJob($request->all(), $infoBot))->onQueue($infoBot['queue_webhook'] ?? 'default');
                }
            }

        }
        return new Response([true], 200);
    }
}
