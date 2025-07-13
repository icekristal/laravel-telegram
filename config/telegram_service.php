<?php
return [
    'bots' => [
        'first_bot' => [
            'name_bot' => 'first_bot',
            'is_queue_handler' => env('TELEGRAM_BOT_IS_QUEUE_HANDLER', true),
            'is_save_database' => env('TELEGRAM_BOT_IS_SAVE_DATABASE', false),
            'is_technical_job' => env('TELEGRAM_BOT_IS_TECHNICAL_JOB', false),
            'db_connection' => env('DB_CONNECTION', 'mysql'),
            'is_save_files' => false,
            'is_save_answer' => false,
            'is_send_answer_group_only_entities_bot' => true, //send message only entities bot "@name_bot text text"
            'path_save_files' => 'storage/telegram/',
            'url' => 'https://t.me/' . env('TELEGRAM_BOT_NAME', 'first_bot'),
            'name' => env('TELEGRAM_BOT_NAME', null),
            'token' => env('TELEGRAM_BOT_TOKEN', null),
            'webhook_domain' => env('TELEGRAM_WEBHOOK_DOMAIN', 'localhost'), //null - main domain
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', 'ice_telegram_url'),
            'queue_webhook' => 'webhook_telegram', //null - no active queue
            'queue_send' => 'webhook_telegram', //null - no active queue
            'main_telegram_server_url' => env('TELEGRAM_SERVER_URL', 'https://api.telegram.org'),


            'method_messages' => [
                'text' => App\Services\DefaultBotTelegramHandle\TextTelegramHandle::class,
                'video' => App\Services\DefaultBotTelegramHandle\VideoHandle::class,
                'video_note' => App\Services\DefaultBotTelegramHandle\VideoNoteHandle::class,
                'voice' => App\Services\DefaultBotTelegramHandle\VoiceHandle::class,
                'photo' => App\Services\DefaultBotTelegramHandle\PhotoHandle::class,
                'document' => App\Services\DefaultBotTelegramHandle\DocumentHandle::class,
                'location' => App\Services\DefaultBotTelegramHandle\LocationHandle::class,
            ],
            'method_callback_query' => App\Services\DefaultBotTelegramHandle\CallbackQueryHandle::class,
            'method_message_reaction' => App\Services\DefaultBotTelegramHandle\MessageReactionHandle::class,
        ],
    ],

    'default_bot' => 'first_bot',

    'is_enable_webhook_cache' => env('TELEGRAM_BOT_IS_WEBHOOK_CACHE', false),

];
