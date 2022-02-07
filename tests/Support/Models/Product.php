<?php

namespace Nevadskiy\Translatable\Tests\Support\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;
use Nevadskiy\Translatable\Strategies\AdditionalTableStrategy;
use Nevadskiy\Translatable\Translator;
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
    use HasTranslations;

    /**
     * The attributes that can be translatable.
     *
     * @var array
     */
    protected $translatable = [
        'title',
        'description',
    ];

    public function translation(): Translator
    {
        return new Translator(new AdditionalTableStrategy());
    }
}
