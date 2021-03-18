<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Scopes\TranslationsEagerLoadScope;

/**
 * @mixin Model
 * @mixin HasTranslations
 */
trait TranslationScopes
{
    /**
     * Scope to remove the 'translations' relation from a query.
     */
    protected function scopeWithoutTranslations(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TranslationsEagerLoadScope::class);
    }

    /**
     * Scope to filter models by translatable attribute.
     *
     * @param mixed $value
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

            $query->orWhereHas('translations', function (Builder $query) use ($attribute, $value, $locale, $operator) {
                $query->forAttribute($attribute);

                if ($locale) {
                    $query->forLocale($locale);
                }

                $query->where('value', $operator, $value);
            });
        });
    }

    /**
     * Scope to order models by translatable attribute.
     */
    protected function scopeOrderByTranslatable(Builder $query, string $attribute, string $direction = 'asc', string $locale = null): Builder
    {
        $locale = $locale ?: static::getTranslator()->getLocale();

        if (static::getTranslator()->isDefaultLocale($locale)) {
            return $query->orderBy($attribute, $direction);
        }

        return $query->orderBy(
            Translation::query()
                ->whereColumn('translatable_id', "{$this->getTable()}.{$this->getKeyName()}")
                ->where('translatable_type', $this->getMorphClass())
                ->forLocale($locale)
                ->forAttribute($attribute)
                ->active()
                ->select('value'),
            $direction
        );
    }
}
