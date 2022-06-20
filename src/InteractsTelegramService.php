<?php

namespace Icekristal\LaravelTelegram;

use Icekristal\LaravelTelegram\Models\ServiceTelegram;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsTelegramService
{
    /**
     *
     * @return MorphOne
     */
    public function telegram(): MorphOne
    {
        return $this->morphOne(ServiceTelegram::class, 'owner');
    }

    /**
     *
     * @return MorphMany
     */
    public function telegrams(): MorphMany
    {
        return $this->morphMany(ServiceTelegram::class, 'owner');
    }
}
