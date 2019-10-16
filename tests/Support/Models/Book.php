<?php

namespace Nevadskiy\Translatable\Tests\Support\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\HasTranslations;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property string description_short
 * @property Collection translations
 * @property Carbon created_at
 */
class Book extends Model
{
    use HasTranslations;

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
        'title',
        'description',
    ];

    /**
     * Get description short attribute.
     *
     * @return string
     */
    public function getDescriptionShortAttribute(): string
    {
        return Str::limit($this->description, 3);
    }

    /**
     * Get title attribute.
     *
     * @param string $title
     * @return string
     */
    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }

    /**
     * Set title attribute.
     *
     * @param string $title
     */
    public function setTitleAttribute(string $title): void
    {
        $this->attributes['title'] = Str::limit($title, 30);
    }
}
