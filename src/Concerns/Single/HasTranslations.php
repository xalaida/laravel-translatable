<?php

namespace Nevadskiy\Translatable\Concerns\Single;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Nevadskiy\Translatable\Concerns\InteractsWithTranslations;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Scopes\TranslationsEagerLoadScope;
use Nevadskiy\Translatable\Strategies\SingleTableStrategy;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * @mixin Model
 * @property Collection|Translation[] translations
 */
trait HasTranslations
{
    use InteractsWithTranslations;

    /**
     * Boot the trait.
     */
    protected static function bootHasTranslations(): void
    {
        static::addGlobalScope(new TranslationsEagerLoadScope());

        static::saved(static function (self $translatable) {
            $translatable->handleSavedEvent();
        });

        static::deleted(static function (self $translatable) {
            $translatable->handleDeletedEvent();
        });
    }

    /**
     * Get the translation strategy.
     */
    protected function getTranslationStrategy(): TranslatorStrategy
    {
        return new SingleTableStrategy($this);
    }

    /**
     * Get the translations' relation.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Handle the model "saved" event.
     */
    protected function handleSavedEvent(): void
    {
        $this->translation()->save();
    }

    /**
     * Handle the model deleted event.
     */
    protected function handleDeletedEvent(): void
    {
        if ($this->shouldDeleteTranslations()) {
            $this->deleteTranslations();
        }
    }

    /**
     * Determine whether the model should delete translations.
     */
    protected function shouldDeleteTranslations(): bool
    {
        if (! $this->isUsingSoftDeletes()) {
            return true;
        }

        if ($this->isForceDeleting()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the model uses soft deletes.
     */
    protected function isUsingSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this), true);
    }

    /**
     * Delete the model translations.
     */
    protected function deleteTranslations(): void
    {
        $this->translations()->delete();
    }

    // TODO: refactor below.

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
    protected function scopeWhereTranslatable(Builder $query, string $attribute, $value, string $locale = null, string $operator = '='): Builder
    {
        return $query->where(function (Builder $query) use ($attribute, $value, $locale, $operator) {
            if (is_null($locale) || $this->translation()->isFallbackLocale($locale)) {
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
        $locale = $locale ?: $this->translation()->getLocale();

        if ($this->translation()->isFallbackLocale($locale)) {
            return $query->orderBy($attribute, $direction);
        }

        return $query->orderBy(
            Translation::query()
                ->whereColumn('translatable_id', "{$this->getTable()}.{$this->getKeyName()}")
                ->where('translatable_type', $this->getMorphClass())
                ->forLocale($locale)
                ->forAttribute($attribute)
                ->limit(1)
                ->select('value'),
            $direction
        );
    }
}
