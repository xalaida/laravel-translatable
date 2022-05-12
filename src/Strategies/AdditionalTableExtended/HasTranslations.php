<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTableExtended;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Nevadskiy\Translatable\Strategies\AdditionalTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\AdditionalTableExtended\Scopes\TranslationsEagerLoadingScope;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslator;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * @mixin Model
 * @property Collection|Translation[] translations
 */
trait HasTranslations
{
    use InteractsWithTranslator;

    /**
     * Boot the trait.
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
    }

    /**
     * Get the translation strategy.
     */
    protected function getTranslationStrategy(): TranslatorStrategy
    {
        return new AdditionalTableExtendedStrategy($this);
    }

    /**
     * Get relation to entity translations.
     */
    public function translations(): HasMany
    {
        $instance = $this->getEntityTranslationInstance();

        return $this->newHasMany(
            $instance->newQuery(),
            $this,
            $instance->qualifyColumn($this->getEntityTranslationForeignKey()),
            $this->getKeyName()
        );
    }

    /**
     * Get the entity translation model instance.
     */
    protected function getEntityTranslationInstance(): Translation
    {
        $instance = $this->newRelatedInstance(Translation::class);

        $instance->setTable($this->getEntityTranslationTable());

        return $instance;
    }

    /**
     * Get the table name of the entity translation.
     */
    protected function getEntityTranslationTable(): string
    {
        return $this->joiningTableSegment().'_translations';
    }

    /**
     * Get the foreign key of the entity translation table.
     */
    protected function getEntityTranslationForeignKey(): string
    {
        return $this->getForeignKey();
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
    protected function scopeWhereTranslatable(Builder $query, string $attribute, $value, string $locale = null, string $operator = '=', string $boolean = 'and'): Builder
    {
        $this->translator()->assertAttributeIsTranslatable($attribute);

        if (is_null($locale)) {
            return $query->where(function (Builder $query) use ($attribute, $value, $operator) {
                $query->where($attribute, $operator, $value)
                    ->orWhereHas('translations', function (Builder $query) use ($attribute, $value, $operator) {
                        $query->where($attribute, $operator, $value);
                    });
            }, null, null, $boolean);
        }

        if ($this->translator()->isFallbackLocale($locale)) {
            return $query->where($attribute, $operator, $value, $boolean);
        }

        return $query->has('translations', '>=', 1, $boolean, function (Builder $query) use ($attribute, $value, $locale, $operator) {
            $query->where($attribute, $operator, $value)
                ->when($locale, function (Builder  $query) use ($locale) {
                    $query->forLocale($locale);
                });
        });
    }

    /**
     * Scope to order models by translatable attribute.
     */
    protected function scopeOrderByTranslatable(Builder $query, string $attribute, string $direction = 'asc', string $locale = null): Builder
    {
        $this->translator()->assertAttributeIsTranslatable($attribute);

        $locale = $locale ?: $this->translator()->getLocale();

        if ($this->translator()->isFallbackLocale($locale)) {
            return $query->orderBy($attribute, $direction);
        }

        $translation = $this->getEntityTranslationInstance();

        if (! $query->getQuery()->columns) {
            $query->addSelect($this->qualifyColumn('*'));
        }

        $query->leftJoin($translation->getTable(), function (JoinClause $join) use ($translation, $locale) {
            $join->on($translation->qualifyColumn($this->getEntityTranslationForeignKey()), '=', $this->qualifyColumn($this->getKeyName()))
                ->where($translation->qualifyColumn('locale'), $locale);
        });

        return $query->orderBy($translation->qualifyColumn($attribute), $direction);
    }
}
