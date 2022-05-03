<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Nevadskiy\Translatable\Strategies\AdditionalTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslator;
// use Nevadskiy\Translatable\Strategies\SingleTable\Scopes\TranslationsEagerLoadScope;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use function collect;

/**
 * @mixin Model
 * @mixin SoftDeletes
 * @property Collection|Translation[] translations
 */
trait HasTranslations
{
    use InteractsWithTranslator;

    /**
     * Boot the trait.
     */
    protected static function bootHasEntityTranslations(): void
    {
        // static::addGlobalScope(new TranslationsEagerLoadScope());

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
        return new AdditionalTableStrategy($this);
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
        // TODO: probably move to the translation instance.

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
        return collect(class_uses_recursive($this))->contains(SoftDeletes::class);
    }

    /**
     * Delete the model translations.
     */
    protected function deleteTranslations(): void
    {
        $this->translations()->delete();
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        if (! $this->shouldResolveRouteBindingUsingTranslations($field)) {
            return parent::resolveRouteBinding($value, $field);
        }

        // TODO: feature resolving model by translatable attribute. (possible two ways: if missing use fallback or 404) so maybe do not implement this method.

        $locale = $this->translator()->getLocale();

        $model = $this->whereTranslatable($field, $value, $locale)->first();

        if ($model) {
            return $model;
        }

        return $this->newQuery()
            ->where($field, $value)
            ->whereDoesntHave('translations', function ($query) use ($field, $locale) {
                $query->forAttribute($field);
                $query->forLocale($locale);
            })
            ->first();
    }

    protected function shouldResolveRouteBindingUsingTranslations(string $field): bool
    {
        if (! $this->isTranslatable($field)) {
            return false;
        }

        return ! $this->translator()->isFallbackLocale();
    }
}
