<?php

namespace Icekristal\LaravelTelegram;

use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsTelegramService
{
    /**
     *
     * @return MorphMany
     */
    public function telegram(): MorphMany
    {
        return $this->morphMany(ServiceTelegram::class, 'owner');
    }
}
