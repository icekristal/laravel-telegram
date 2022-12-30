install:
```php
composer require icekristal/laravel-telegram
```
migration:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\TelegramServiceProvider" --tag="migrations"
```

config:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\TelegramServiceProvider" --tag="config"
```

default handle:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\TelegramServiceProvider" --tag="translations"
```

lang:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\TelegramServiceProvider" --tag="ice_telegram_default_handlers"
```


use:
```php
use Icekristal\LaravelTelegram\InteractsTelegramService;

class User extends Model
{
    use InteractsTelegramService;
}

$modelUser->telegram->chat_id;
```

send message, if chat telegram have owner
```php
$modelUser->sendTelegramMessage('text message');
```

in config:
```php
'method_messages' => [
    'text' => App\Services\DefaultBotTelegramHandle\TextTelegramHandle::class,
],
```

example handle text:
```php
class TextTelegramHandle extends MainTelegramHandle
{
    public function __construct($data, $botInfo)
    {
        parent::__construct($data, $botInfo);
        $text = $data['text'] ?? '';
        if (Str::startsWith($text, '/start')) {
            $this->returnTextStart();
        } elseif (Str::startsWith($text, '/menu')) {
            $this->returnMenu();
        }  else {
            $this->parseOtherText();
        }
        
        $this->onlyMessage = "send only message";
        $this->message = "send message";
        $this->keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => __('text_line_1') . " ✌️", "callback_data" => "callback_line_1"],
                ],
                [
                    ["text" => __('text_line_2') . " ✌️", "callback_data" => "callback_line_2"],
                ]
            ]
        ];
        $this->image = "URL image";
        $this->file = "URL file";
    }
}

class MainTelegramHandle
{

    public $onlyMessage = null;
    public $message = null;
    public $keyboard = null;
    public $image = null;
    public $file = null;

    public function __construct($data, $botInfo)
    {

    }

    public function getResult(): array
    {
        return [
            'only_message' => $this->onlyMessage,
            'callback_message' => $this->messageCallback,
            'message' => $this->message,
            'keyboard' => $this->keyboard,
            'image' => $this->image,
            'file' => $this->file,
            'show_alert' => $this->showAlert,
            'cache_time' => $this->cacheTime,
            'url' => $this->url,
        ];
    }
}
```


v3 >  
Notification
```php
    public function via($notifiable): array
    {
        return [\Icekristal\LaravelTelegram\Channels\TelegramChannel::class];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return (new \Icekristal\LaravelTelegram\Channels\Messages\TelegramMessage())->content("Text");
    }
```

Facade Telegram:
```php
IceTelegram::setInfoBot(array $infoBot);
IceTelegram::setChatId(mixed $chatId);
IceTelegram::setParams(array $params);
IceTelegram::setOwner(array $owner);
IceTelegram::sendMessage();
IceTelegram::deleteMessage();
IceTelegram::sendCallback();
IceTelegram::sendPhoto();
IceTelegram::sendDocument();
IceTelegram::sendLocation();
IceTelegram::sendQR();
```
