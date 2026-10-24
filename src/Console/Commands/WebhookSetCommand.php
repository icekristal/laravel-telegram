<?php

use Illuminate\Console\Command;

class WebhookSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ice-telegram:set-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Устанавливаем веб хуки';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $bots = config('telegram_service.bots') ?? [];

        if (empty($bots)) {
            $this->warn('No bots found in config.');
            return;
        }

        foreach ($bots as $keyBot => $botInfo) {
            $isSetWebhook = $this->confirm("Are you sure you want to SET webhook '{$keyBot}' ?");
            if (!$isSetWebhook) continue;

            $server = rtrim($botInfo['main_telegram_server_url'] ?? 'https://api.telegram.org', '/');
            $token = $botInfo['token'] ?? null;
            $secretWebhookKey = $botInfo['secret_webhook_key'] ?? null;
            $domain = $botInfo['webhook_domain'] ?? null;
            $domainPath = $botInfo['webhook_url'] ?? null;

            if (empty($token)) {
                $this->error("Token for bot '{$keyBot}' is missing. Skipped.");
                continue;
            }

            if (empty($domain) || empty($domainPath)) {
                $this->error("Domain or webhook_url for bot '{$keyBot}' is missing in config. Skipped.");
                continue;
            }

            $webhookUrl = rtrim($domain, '/') . '/' . ltrim($domainPath, '/');

            $payload = [
                'url' => $webhookUrl,
            ];

            if (!empty($secretWebhookKey)) {
                $payload['secret_token'] = $secretWebhookKey;
            }

            $this->info("Setting webhook for '{$keyBot}' to {$webhookUrl}...");

            try {
                $response = Http::post("{$server}/bot{$token}/setWebhook", $payload);

                if ($response->successful() && $response->json('ok')) {
                    $this->info("✅ Webhook for '{$keyBot}' successfully set!");
                } else {
                    $errorMsg = $response->json('description') ?? 'Unknown Telegram error';
                    $this->error("❌ Failed to set webhook for '{$keyBot}': {$errorMsg}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Network error while connecting to Telegram API: " . $e->getMessage());
            }
        }
    }
}
