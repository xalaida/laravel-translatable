<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslations;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\SingleTable\Scopes\TranslationsJoinScope;
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
        // TODO: move booting to the strategy.

        static::addGlobalScope(new TranslationsJoinScope());

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
        $this->translator()->save();
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
     * TODO: probably move to the strategy instance.
     */
    protected function shouldDeleteTranslations(): bool
    {
        if (! collect(class_uses_recursive($this))->contains(SoftDeletes::class)) {
            return true;
        }

        if ($this->isForceDeleting()) {
            return true;
        }

        return false;
    }

    /**
     * Delete the model translations.
     * TODO: probably move to the strategy instance.
     */
    protected function deleteTranslations(): void
    {
        $this->translations()->delete();
    }

    /**
     * Scope to remove the 'translations' relation from a query.
     */
    protected function scopeWithoutTranslations(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TranslationsEagerLoadScope::class);
    }

    /**
     * Scope to filter models by translatable attribute.
     * TODO: add possibility to use qualified columns (table.attribute)
     */
    protected function scopeWhereTranslatable(Builder $query, string $attribute, $value, string $locale = null, string $operator = '='): Builder
    {
        $this->translator()->assertAttributeIsTranslatable($attribute);

        if (is_null($locale)) {
            return $query->where(function (Builder $query) use ($attribute, $value, $operator) {
                $query->where($attribute, $operator, $value)
                    ->orWhereHas('translations', function (Builder $query) use ($attribute, $value, $operator) {
                        $query->where('value', $operator, $value)
                            ->forAttribute($attribute);
                    });
            });
        }

        if ($this->translator()->isFallbackLocale($locale)) {
            return $query->where($attribute, $operator, $value);
        }

        return $query->whereHas('translations', function (Builder $query) use ($attribute, $value, $locale, $operator) {
            $query->where('value', $operator, $value)
                ->forAttribute($attribute)
                ->forLocale($locale);
        });
    }

    /**
     * Scope to order models by translatable attribute.
     * TODO: add possibility to use qualified columns (table.attribute)
     */
    protected function scopeOrderByTranslatable(Builder $query, string $attribute, string $direction = 'asc', string $locale = null): Builder
    {
        $this->translator()->assertAttributeIsTranslatable($attribute);

        $locale = $locale ?: $this->translator()->getLocale();

        if ($this->translator()->isFallbackLocale($locale)) {
            return $query->orderBy($attribute, $direction);
        }

        // TODO: resolve model using resolver
        $translationModel = new Translation;

        return $query->leftJoin($translationModel->getTable(), function (JoinClause $join) use ($translationModel, $attribute, $locale) {
            $join->on($translationModel->qualifyColumn('translatable_id'), '=', $this->qualifyColumn($this->getKeyName()))
                ->where($translationModel->qualifyColumn('translatable_type'), $this->getMorphClass())
                ->where($translationModel->qualifyColumn('translatable_attribute'), $attribute)
                ->where($translationModel->qualifyColumn('locale'), $locale);
        })
            ->addSelect($this->qualifyColumn('*'))
            ->orderBy($translationModel->qualifyColumn('value'), $direction);
    }

    /**
     * TODO: add possibility to use qualified columns (table.attribute)
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
}
