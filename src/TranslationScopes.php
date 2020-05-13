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
     * Scope to filter models by translation.
     *
     * @param Builder $query
     * @param string $attribute
     * @param $value
     * @param string|null $locale
     * @return Builder
     */
    protected function scopeWhereTranslatable(Builder $query, string $attribute, $value, string $locale = null): Builder
    {
        return $query->whereHas('translations', function (Builder $query) use ($attribute, $value, $locale) {
            $query->forAttribute($attribute);
            $query->forLocale($locale ?: static::getTranslator()->getLocale());
            $query->where('value', $value);
        });
    }
}
