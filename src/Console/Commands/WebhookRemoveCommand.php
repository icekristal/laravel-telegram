<?php
namespace Icekristal\LaravelTelegram\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WebhookRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ice-telegram:remove-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляет вебхуки из указанных ботов или всех ботов по умолчанию';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $bots = config('telegram_service.bots');

        if (empty($bots)) {
            $this->warn('No bots found in config.');
            return;
        }

        foreach ($bots as $keyBot => $botInfo) {
            $isDeleteWebhook = $this->confirm("Are you sure you want to delete webhook '{$keyBot}' ?");
            if (!$isDeleteWebhook) continue;

            $server = rtrim($botInfo['main_telegram_server_url'] ?? 'https://api.telegram.org', '/');
            $token = $botInfo['token'] ?? null;

            if (empty($token)) {
                $this->error("Token for bot '{$keyBot}' is missing. Skipped.");
                continue;
            }

            $this->info("Sending request for '{$keyBot}'...");

            try {
                $response = Http::post("{$server}/bot{$token}/deleteWebhook");

                if ($response->successful() && $response->json('ok')) {
                    $this->info("✅ Webhook for '{$keyBot}' successfully deleted!");
                } else {
                    $errorMsg = $response->json('description') ?? 'Unknown Telegram error';
                    $this->error("❌ Failed to delete webhook for '{$keyBot}': {$errorMsg}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Network error while connecting to Telegram API: " . $e->getMessage());
            }
        }
    }
}
