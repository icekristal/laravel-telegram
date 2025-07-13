<?php

namespace Icekristal\LaravelTelegram\Services;


use Icekristal\LaravelTelegram\Models\Deeplink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonException;


class DeeplinkService
{

    public mixed $owner = null;
    public function for($owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     *
     * @param string $action
     * @param mixed  ...$parameters
     *
     * @return string
     */
    public function make(string $action, ...$parameters): string
    {
        $owner = $this->owner ?? Auth::user();

        if ($owner) {
            $parameters[] = sprintf('OWNER-S%sOWNER-E', $owner->id);
        }

        $data = [
            'action'     => $action,
            'parameters' => $parameters
        ];

        try {
            $hash = hash('xxh64', json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (JsonException $e) {
            $hash = Str::uuid();
        }

        $deeplink = Deeplink::query()
            ->firstOrCreate(['hash' => $hash], $data);

        $defaultBot = config('telegram_service.default_bot');

        return sprintf(
            'https://t.me/%s/?start=%s',
            Str::replace('@', '', config('telegram_service.bots')[$defaultBot]['name']),
            $deeplink->hash
        );
    }

    /**
     * Статический диплинк, без обращения к БД и без рефералки
     *
     * @param string $action
     * @param        ...$parameters
     *
     * @return string
     */
    public function static(string $action, ...$parameters): string
    {
        $defaultBot = config('telegram_service.default_bot');

        return sprintf(
            'https://t.me/%s/?start=%s',
            Str::replace('@', '', config('telegram_service.bots')[$defaultBot]['name']),
            $action . (!empty($parameters) ? '_' : null) . implode('_', $parameters)
        );
    }

    /**
     * @param string $hash
     * @return Deeplink|null
     */
    public function find(string $hash): ?Deeplink
    {
        $keyCache = 'tg_deeplink_' . $hash;
        return Cache::remember($keyCache, 60 ,function () use ($hash) {
            return Deeplink::query()->firstWhere('hash', $hash);
        });
    }
}
