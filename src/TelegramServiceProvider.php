<?php

namespace Icekristal\LaravelTelegram;

use Icekristal\LaravelTelegram\Services\DeeplinkService;
use Icekristal\LaravelTelegram\Services\HighIceTelegramService;
use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind('high.ice.telegram', HighIceTelegramService::class);
        $this->app->bind('high.ice.telegram_deeplink', DeeplinkService::class);
        $this->registerConfig();
        $this->registerTranslations();
        $this->registerRoutes();
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfigs();
            $this->publishMigrations();
            $this->publishTranslations();
            $this->publishDefaultHandlers();
        }
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/telegram_service.php', 'telegram_service');
    }


    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'telegram_service');
    }

    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/webhook_telegram.php');
    }

    protected function publishMigrations(): void
    {
        if (!class_exists('CreateServiceTelegramTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_service_telegram_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_service_telegram_table.php'),
            ], 'migrations');
        }
        if (!class_exists('CreateServiceTelegramOwnerMessagesTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_service_telegram_owner_messages_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_service_telegram_owner_messages_table.php'),
            ], 'migrations');
        }
        if (!class_exists('CreateDeeplinksTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_deeplinks_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_deeplinks_table.php'),
            ], 'migrations');
        }
    }

    protected function publishConfigs(): void
    {
        $this->publishes([
            __DIR__ . '/../config/telegram_service.php' => config_path('telegram_service.php'),
        ], 'config');
    }

    protected function publishTranslations()
    {
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang'),
        ], 'translations');
    }

    protected function publishDefaultHandlers()
    {
        $this->publishes([
            __DIR__ . '/Services/DefaultBotTelegramHandle' => app_path('Services/DefaultBotTelegramHandle'),
        ], 'ice_telegram_default_handlers');
    }

}
