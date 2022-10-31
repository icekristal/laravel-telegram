<?php

namespace Icekristal\LaravelTelegram\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property integer $id
 * @property integer $message_id
 * @property integer $chat_id
 * @property string $owner_type
 * @property string $bot_key
 * @property object $other_info
 * @property integer $owner_id
 * @property string $created_at
 * @property string $updated_at
 */
class ServiceTelegramOwnerMessage extends Model
{
    /**
     *
     * Name Table
     * @var string
     */
    protected $table = 'service_telegram_owner_messages';


    protected $fillable = [
        'owner_type', 'owner_id', 'chat_id', 'message_id', 'other_info', 'bot_key'
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
