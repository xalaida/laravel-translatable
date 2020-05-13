<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Scopes\TranslationsEagerLoadScope;

/**
 * @mixin Model
 * @mixin HasTranslations
 */
trait TranslationScopes
{
    /**
     * Scope to remove the 'translations' relation from a query.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function scopeWithoutTranslations(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TranslationsEagerLoadScope::class);
    }

    /**
     * Scope to filter models by translatable attribute.
     *
     * @param Builder $query
     * @param string $attribute
     * @param $value
     * @param string|null $locale
     * @return Builder
     */
    protected function scopeWhereTranslatable(Builder $query, string $attribute, $value, string $locale = null): Builder
    {
        return $query->where(function (Builder $query) use ($attribute, $value, $locale) {
            if (is_null($locale) || static::getTranslator()->isDefaultLocale($locale)) {
                $query->where($attribute, $value);
            }

            if (! static::getTranslator()->isDefaultLocale($locale)) {
                $query->whereTranslation($attribute, $value, $locale);
            }
        });
    }

    /**
     * Scope to filter models by translation.
     *
     * @param Builder $query
     * @param string $attribute
     * @param $value
     * @param string|null $locale
     * @return Builder
     */
    protected function scopeWhereTranslation(Builder $query, string $attribute, $value, string $locale = null): Builder
    {
        return $query->orWhereHas('translations', function (Builder $query) use ($attribute, $value, $locale) {
            $query->forAttribute($attribute);

            if ($locale) {
                $query->forLocale($locale);
            }

            $query->where('value', $value);
        });
    }
}
