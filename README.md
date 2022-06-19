install:
```php
composer require icekristal/laravel-telegram
```
migration:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\InteractsTelegramService" --tag="migrations"
```

config:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\InteractsTelegramService" --tag="config"
```

default handle:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\InteractsTelegramService" --tag="translations"
```

lang:
```php
php artisan vendor:publish --provider="Icekristal\LaravelTelegram\InteractsTelegramService" --tag="ice_telegram_default_handlers"
```


use:
```php
use Icekristal\LaravelTelegram\InteractsTelegramService;

class User extends Model
{
    use InteractsTelegramService;
}

$modelUser->telegram()->chat_id;
