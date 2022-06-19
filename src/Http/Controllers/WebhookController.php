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
            dispatch(new IceTelegramJob($request->all(), $infoBot))->onQueue($infoBot['queue'] ?? 'default');
        }
        return new Response([true], 200);
    }
}
