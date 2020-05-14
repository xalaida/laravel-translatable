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
     * @param string $operator
     * @return Builder
     */
    protected function scopeWhereTranslatable(
        Builder $query,
        string $attribute,
        $value,
        string $locale = null,
        string $operator = '='
    ): Builder {
        return $query->where(function (Builder $query) use ($attribute, $value, $locale, $operator) {
            if (is_null($locale) || static::getTranslator()->isDefaultLocale($locale)) {
                $query->where($attribute, $operator, $value);
            }

            if (is_null($locale) || ! static::getTranslator()->isDefaultLocale($locale)) {
                $query->orWhereHas('translations', function (Builder $query) use ($attribute, $value, $locale, $operator) {
                    $query->forAttribute($attribute);

                    if ($locale) {
                        $query->forLocale($locale);
                    }

                    $query->where('value', $operator, $value);
                });
            }
        });
    }
}
