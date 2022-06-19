<?php

use Icekristal\LaravelTelegram\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

foreach (config('telegram_service.bots') as $nameBot => $bot) {
    if(!is_null($bot['webhook_url'])) {
        Route::get("{$bot['webhook_url']}", [WebhookController::class, 'index'])->name("wh_telegram.{$nameBot}")->domain($bot['webhook_domain']);
    }
}
