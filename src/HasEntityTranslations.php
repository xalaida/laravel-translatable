<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Nevadskiy\Translatable\Models\EntityTranslation;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Scopes\TranslationsEagerLoadScope;
use Nevadskiy\Translatable\Strategies\AdditionalTableStrategy;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * @mixin Model
 * @mixin SoftDeletes
 * @property Collection|Translation[] translations
 */
trait HasEntityTranslations
{
    use Concerns\Translations;

    /**
     * Boot the trait.
     */
    protected static function bootHasEntityTranslations(): void
    {
        static::addGlobalScope(new TranslationsEagerLoadScope());

        static::saved(static function (self $translatable) {
            $translatable->translation()->save();
        });

        static::deleted(static function (self $translatable) {
            if ($translatable->shouldDeleteTranslations()) {
                $translatable->deleteTranslations();
            }
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
     * Get the entity translations' relation.
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
    protected function getEntityTranslationInstance(): EntityTranslation
    {
        $instance = $this->newRelatedInstance(EntityTranslation::class);

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
}
