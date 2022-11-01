<?php

namespace Icekristal\LaravelTelegram\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property integer $id
 * @property integer $chat_id
 * @property string $owner_type
 * @property string $alias
 * @property integer $owner_id
 * @property string $bot_key
 * @property string $created_at
 * @property string $updated_at
 * @property object $other_info
 */
class ServiceTelegram extends Model
{
    /**
     *
     * Name Table
     * @var string
     */
    protected $table = 'service_telegram';


    protected $fillable = [
        'owner_type', 'owner_id', 'chat_id', 'username', 'alias', 'other_info', 'bot_key'
    ];

    /**
     *
     * Mutation
     *
     * @var array
     */
    protected $casts = [
        'other_info' => 'object',
    ];

    /**
     * Owner transaction
     *
     * @return MorphTo
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
