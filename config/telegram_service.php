<?php
return [
    'bots' => [
        'first_bot' => [
            'name_bot' => 'first_bot',
            'is_save_database' => env('TELEGRAM_BOT_IS_SAVE_DATABASE', false),
            'is_technical_job' => env('TELEGRAM_BOT_IS_TECHNICAL_JOB', false),
            'is_save_files' => false,
            'path_save_files' => 'storage/telegram/',
            'url' => 'https://t.me/' . env('TELEGRAM_BOT_NAME', 'first_bot'),
            'name' => env('TELEGRAM_BOT_NAME', null),
            'token' => env('TELEGRAM_BOT_TOKEN', null),
            'webhook_domain' => env('TELEGRAM_WEBHOOK_DOMAIN', null), //null - main domain
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', null),
            'queue' => 'webhook_telegram', //null - no active queue

            'method_messages' => [
                'text' => App\Services\DefaultBotTelegramHandle\TextTelegramHandle::class,
                'video' => App\Services\DefaultBotTelegramHandle\VideoHandle::class,
                'video_note' => App\Services\DefaultBotTelegramHandle\VideoNoteHandle::class,
                'voice' => App\Services\DefaultBotTelegramHandle\VoiceHandle::class,
                'photo' => App\Services\DefaultBotTelegramHandle\PhotoHandle::class,
                'document' => App\Services\DefaultBotTelegramHandle\DocumentHandle::class,
            ],
            'method_callback_query' => App\Services\DefaultBotTelegramHandle\CallbackQueryHandle::class,
        ],
    ],

    'default_bot' => 'first_bot',

];
