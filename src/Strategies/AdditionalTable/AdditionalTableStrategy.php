<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTable;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\AdditionalTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * TODO: add possibility to trigger an exception when creating model in non-default locale (only for copyingStructure mode).
 */
class AdditionalTableStrategy implements TranslatorStrategy
{
    /**
     * The translatable model instance.
     *
     * @var Model
     */
    private $translatable;

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
    private $pendingTranslations = [];

    /**
     * Make a new strategy instance.
     *
     * @param Model|HasTranslations $translatable
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

    public function save(): void
    {
        foreach ($this->pullPendingTranslations() as $locale => $attributes) {
            $this->translatable->translations()->updateOrCreate(['locale' => $locale], $attributes);
        }
    }

    public function delete(): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * Pull pending translations.
     */
    private function pullPendingTranslations(): array
    {
        $pendingTranslations = $this->pendingTranslations;

        $this->pendingTranslations = [];

        return $pendingTranslations;
    }

    /**
     * Load translation values from the given collection of translations.
     */
    protected function loadTranslations(Collection $translations): void
    {
        $translations->each(function (Translation $translation) {
            foreach ($this->translatable->getTranslatable() as $attribute) {
                $this->translations[$translation->locale][$attribute] = $translation->getAttribute($attribute);
            }
        });
    }

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
}
