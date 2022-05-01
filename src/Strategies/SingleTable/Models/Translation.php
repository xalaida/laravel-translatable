<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;

/**
 * @property int id
 * @property string translatable_type
 * @property int translatable_id
 * @property string translatable_attribute
 * @property-read Model|HasTranslations translatable
 * @property string value
 * @property string locale
 * @property Carbon updated_at
 * @property Carbon created_at
 */
class Translation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'translatable_attribute',
        'locale',
        'value',
    ];

    /**
     * Get the translatable entity.
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope translations by the given locale.
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope translations by the given attribute.
     */
    public function scopeForAttribute(Builder $query, string $attribute): Builder
    {
        return $query->where('translatable_attribute', $attribute);
    }
}
