<?php

namespace Icekristal\LaravelTelegram\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property integer $id
 * @property string $owner_type
 * @property integer $owner_id
 * @property integer $type
 * @property float $amount
 * @property float $commission
 * @property string $who_type
 * @property integer $who_id
 * @property string $code_currency
 * @property string $signed_amount
 * @property string $named_type
 * @property string $balance_type
 * @property string $created_at
 * @property string $updated_at
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
        'owner_type', 'owner_id', 'chat_id', 'username', 'alias', 'other_info'
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
