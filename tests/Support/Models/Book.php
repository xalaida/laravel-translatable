<?php

namespace Nevadskiy\Translatable\Tests\Support\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;

/**
 * TODO: remove this model.
 *
 * @property int id
 * @property string title
 * @property string description
 * @property int version
 * @property string description_short
 * @property Collection translations
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Book extends Model
{
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

    /**
     * Get title attribute.
     */
    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }
}
