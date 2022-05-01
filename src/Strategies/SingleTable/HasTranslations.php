<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslations;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\SingleTable\Scopes\TranslationsEagerLoadingScope;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * @mixin Model
 * @property Collection|Translation[] translations
 */
trait HasTranslations
{
    use InteractsWithTranslations;

    /**
     * Boot the translations' trait.
     */
    protected static function bootHasTranslations(): void
    {
        static::addGlobalScope(new TranslationsEagerLoadingScope());

        static::saved(static function (self $translatable) {
            $translatable->handleSavedEvent();
        });

        static::deleted(static function (self $translatable) {
            $translatable->handleDeletedEvent();
        });

        // TODO: add replicated handler.
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
        return $this->morphMany(SingleTableStrategy::model(), 'translatable');
    }

    /**
     * Handle the model "saved" event.
     */
    protected function handleSavedEvent(): void
    {
        $this->translator()->save();
    }

    /**
     * Handle the model deleted event.
     */
    protected function handleDeletedEvent(): void
    {
        $this->translator()->delete();
    }

    /**
     * @inheritDoc
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field = $field ?? $this->getRouteKeyName();

        if (! $this->isTranslatable($field)) {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->whereTranslatable($field, $value)->first();
    }

    /**
     * Scope to remove translations eager loading from a query.
     */
    protected function scopeWithoutTranslationsScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TranslationsEagerLoadingScope::class);
    }

    /**
     * Scope to filter models by translatable attribute.
     */
    protected function scopeWhereTranslatable(Builder $query, string $attribute, $value, string $locale = null, string $operator = '='): Builder
    {
        $this->translator()->assertAttributeIsTranslatable($attribute);

        return $query->whereHas('translations', function (Builder $query) use ($attribute, $value, $locale, $operator) {
            $query->forAttribute($attribute)
                ->when($locale, function (Builder  $query) use ($locale) {
                    $query->forLocale($locale);
                })
                ->where('value', $operator, $value);
        });
    }

    /**
     * Scope to order models by translatable attribute.
     */
    protected function scopeOrderByTranslatable(Builder $query, string $attribute, string $direction = 'asc', string $locale = null): Builder
    {
        $this->translator()->assertAttributeIsTranslatable($attribute);

        $locale = $locale ?: $this->translator()->getLocale();

        $translation = resolve(SingleTableStrategy::model());

        return $query->leftJoin($translation->getTable(), function (JoinClause $join) use ($translation, $attribute, $locale) {
            $join->on($translation->qualifyColumn('translatable_id'), '=', $this->qualifyColumn($this->getKeyName()))
                ->where($translation->qualifyColumn('translatable_type'), $this->getMorphClass())
                ->where($translation->qualifyColumn('translatable_attribute'), $attribute)
                ->where($translation->qualifyColumn('locale'), $locale);
        })
            // TODO: add condition if there are currently selected columns
            ->addSelect($this->qualifyColumn('*'))
            ->orderBy($translation->qualifyColumn('value'), $direction);
    }
}
