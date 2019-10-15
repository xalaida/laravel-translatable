<?php

namespace Nevadskiy\Translatable\Tests\Support\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

/**
 * @property int id
 * @property Collection translations
 * @property array body
 */
class Block extends Model
{
    use HasTranslations;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'body' => 'array'
    ];

    /**
     * The attributes that can be translatable.
     *
     * @var array
     */
    protected $translatable = [
        'type',
        'body',
    ];
}
