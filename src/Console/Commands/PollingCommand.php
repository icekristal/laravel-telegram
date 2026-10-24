<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class PollingCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'ice-telegram:polling';
    protected $description = 'Запускает пуллинг обновлений телеграм ботов (Long Polling)';

    protected bool $pool = true;

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Starting Telegram Long Polling...');

        $botsConfig = config('telegram_service.bots') ?? [];
        $activeBots = [];

        foreach ($botsConfig as $name => $botConfig) {
            if (!empty($botConfig['token'])) {
                $activeBots[$name] = [
                    'token' => $botConfig['token'],
                    'server' => rtrim($botConfig['main_telegram_server_url'] ?? 'https://api.telegram.org', '/'),
                    'offset' => 0,
                ];
            }
        }

        if (empty($activeBots)) {
            $this->warn('No bots with tokens found in config.');
            return;
        }

        while ($this->pool) {
            $responses = Http::pool(function (Pool $pool) use ($activeBots) {
                $requests = [];

                foreach ($activeBots as $name => $bot) {
                    $url = "{$bot['server']}/bot{$bot['token']}/getUpdates";

                    $requests[] = $pool->as($name)
                        ->timeout(35)
                        ->get($url, [
                            'offset' => $bot['offset'],
                            'limit' => 100,
                            'timeout' => 30,
                        ]);
                }

                return $requests;
            });

            foreach ($responses as $name => $response) {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->ok() && $response->json('ok')) {
                    $updates = $response->json('result') ?? [];

                    foreach ($updates as $update) {
                        $this->handleUpdate($name, $update);

                        $activeBots[$name]['offset'] = max($activeBots[$name]['offset'], $update['update_id'] + 1);
                    }
                } elseif ($response instanceof \Exception) {
                    if (!str_contains($response->getMessage(), 'cURL error 28')) {
                        Log::error("Polling error for bot {$name}: " . $response->getMessage());
                    }
                }
            }
        }
    }

    /**
     * @param string $botName
     * @param array $update
     * @return void
     */
    private function handleUpdate(string $botName, array $update): void
    {
        $updateId = $update['update_id'];
        $messageSnippet = $update['message']['text'] ?? $update['callback_query']['data'] ?? $update['inline_query']['query'] ?? 'event';

        $this->info("Bot '{$botName}': Update {$updateId} [{$messageSnippet}]");

        $routeName = "wh_telegram.{$botName}";

        if (!Route::has($routeName)) {
            $this->error("Failed to forward: Route '{$routeName}' is not defined.");
            return;
        }

        $url = route($routeName);

        try {
            $response = Http::timeout(10)->post($url, $update);

            if ($response->failed()) {
                $this->error("Failed to forward update {$updateId} to {$url}");
                $this->error($response->body());
            }
        } catch (\Exception $e) {
            $this->error("Exception while forwarding update {$updateId}: " . $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getSubscribedSignals(): array
    {
        $signals = [];
        if (defined('SIGINT')) $signals[] = SIGINT;
        if (defined('SIGTERM')) $signals[] = SIGTERM;
        if (defined('SIGQUIT')) $signals[] = SIGQUIT;

        return $signals;
    }

    /**
     * @param int $signal
     * @param int|false $previousExitCode
     * @return int|false
     */
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        $this->warn("\nShutting down Telegram polling gracefully...");
        $this->pool = false;

        return $previousExitCode;
    }
}
