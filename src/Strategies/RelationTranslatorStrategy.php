<?php

namespace Nevadskiy\Translatable\Strategies;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;

abstract class RelationTranslatorStrategy implements TranslatorStrategy
{
    /**
     * The translatable model instance.
     *
     * @var Model
     */
    protected $translatable;

    /**
     * Indicates if the translation state is booted.
     */
    protected $booted = false;

    /**
     * A list of cached translation.
     *
     * @var array
     */
    protected $translations = [];

    /**
     * A list of pending translation insertions.
     *
     * @var array
     */
    protected $pendingTranslations = [];

    /**
     * Make a new strategy instance.
     */
    public function __construct(Model $translatable)
    {
        $this->translatable = $translatable;
    }

    /**
     * @inheritdoc
     */
    public function get(string $attribute, string $locale)
    {
        $this->bootIfNotBooted();

        if (! array_key_exists($locale, $this->translations)) {
            $this->translations[$locale] = [];
            $this->loadTranslationsForLocale($locale);
        }

        if (! array_key_exists($attribute, $this->translations[$locale])) {
            throw new TranslationMissingException($this->translatable, $attribute, $locale);
        }

        return $this->translations[$locale][$attribute];
    }

    /**
     * @inheritdoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        $this->translations[$locale][$attribute] = $value;
        $this->pendingTranslations[$locale][$attribute] = $value;
    }

    /**
     * This method could be replaced with a direct 'loadTranslations' call on Eloquent' "retrieved" event.
     * But the "retrieved" event is fired BEFORE eager loading, so it is impossible now.
     * @see https://github.com/laravel/framework/issues/29658
     */
    protected function bootIfNotBooted(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        if ($this->translatable->relationLoaded('translations')) {
            $this->loadTranslations($this->translatable->translations);
        }
    }

    /**
     * @inheritdoc
     */
    public function save(): void
    {
        $this->saveTranslations($this->pullPendingTranslations());
    }

    /**
     * @inheritdoc
     */
    public function delete(): void
    {
        if ($this->shouldDeleteTranslations()) {
            $this->deleteTranslations();
        }
    }

    /**
     * Load translation values from the given collection of translations.
     */
    abstract protected function loadTranslations(Collection $translations): void;

    /**
     * Load translation values from the given collection of translations.
     */
    abstract protected function saveTranslations(array $translations): void;

    /**
     * Load translation values for the given locale.
     */
    protected function loadTranslationsForLocale(string $locale): void
    {
        $this->loadTranslations($this->getTranslationsForLocale($locale));
    }

    /**
     * Get translations for the given locale.
     */
    protected function getTranslationsForLocale(string $locale): Collection
    {
        return $this->translatable->translations()
            ->forLocale($locale)
            ->get();
    }

    /**
     * Pull pending translations.
     */
    protected function pullPendingTranslations(): array
    {
        $pendingTranslations = $this->pendingTranslations;

        $this->pendingTranslations = [];

        return $pendingTranslations;
    }

    /**
     * Determine whether the translations should be deleted.
     */
    protected function shouldDeleteTranslations(): bool
    {
        if (! $this->usesSoftDeletes()) {
            return true;
        }

        return $this->translatable->isForceDeleting();
    }

    /**
     * Determine whether the model uses soft deletes.
     */
    protected function usesSoftDeletes(): bool
    {
        return collect(class_uses_recursive($this->translatable))->contains(SoftDeletes::class);
    }

    /**
     * Delete the model translations.
     */
    protected function deleteTranslations(): void
    {
        $this->translatable->translations()->delete();
    }
}
