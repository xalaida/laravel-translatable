<?php

namespace Nevadskiy\Translatable\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nevadskiy\Translatable\HasTranslations;

/**
 * @property int id
 * @property string body
 * @property Carbon|null deleted_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Post extends Model
{
    use SoftDeletes,
        HasTranslations;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that can be translatable.
     *
     * @var array
     */
    protected $translatable = [
        'body',
    ];
}
