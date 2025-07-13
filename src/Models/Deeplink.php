<?php

namespace Icekristal\LaravelTelegram\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $hash
 * @property string $action
 * @property array $parameters
 *
 * @property string $decoded
 *
 */
class Deeplink extends Model
{
    protected $fillable = [
        'hash',
        'action',
        'parameters'
    ];

    protected $casts = [
        'parameters' => 'array'
    ];

    public $timestamps = false;

    protected function decoded(): Attribute
    {
        return Attribute::get(
            fn() => $this->action . (empty($this->parameters) ? '' : '_' . implode('_', $this->parameters))
        );
    }
}
