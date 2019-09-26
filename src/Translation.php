<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property string locale
 * @property Collection translations
 */
class Translation extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Translatable morph relation.
     *
     * @return MorphTo
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

//    public function scopeLocale(Builder $builder, string $locale)
//    {
//        return $builder->where('locale', $locale);
//    }
//
//    public function scopeAttribute(Builder $builder, string $attribute)
//    {
//        return $builder->where('translatable_attribute', $attribute);
//    }
//
//    public function isLocale(string $locale): bool
//    {
//        return $this->locale === $locale;
//    }
//
//    public function isAttribute(string $attribute): bool
//    {
//        return $this->translatable_attribute === $attribute;
//    }
}
