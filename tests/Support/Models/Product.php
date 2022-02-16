<?php

namespace Nevadskiy\Translatable\Tests\Support\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasEntityTranslations;
use Nevadskiy\Uuid\Uuid;

/**
 * @property string id
 * @property string title
 * @property string description
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Product extends Model
{
    use Uuid;
    use HasEntityTranslations;

    /**
     * The attributes that can be translatable.
     *
     * @var array
     */
    protected $translatable = [
        'title',
        'description',
    ];
}
