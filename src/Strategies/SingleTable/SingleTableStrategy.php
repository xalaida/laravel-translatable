<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

class SingleTableStrategy implements TranslatorStrategy
{
    /**
     * The default mode class of the strategy.
     *
     * @var string
     */
    private static $model = Translation::class;

    /**
     * The translatable model instance.
     *
     * @var Model|HasTranslations
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
     * Specify the translation model class.
     */
    public static function useModel(string $model): void
    {
        if (! is_a($model, Translation::class, true)) {
            throw new InvalidArgumentException("A custom translation model must extend the base translation model.");
        }

        static::$model = $model;
    }

    /**
     * Get the model class.
     */
    public static function model(): string
    {
        return static::$model;
    }

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
     * @inheritdoc
     */
    public function save(): void
    {
        foreach ($this->pendingTranslations as $locale => $attributes) {
            foreach ($attributes as $attribute => $value) {
                $this->updateOrCreateTranslation($attribute, $locale, $value);
            }
        }

        $this->pendingTranslations = [];
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
     * This method could be replaced with a direct 'loadTranslations' call on Eloquent' "retrieved" event.
     * But the "retrieved" event is fired BEFORE eager loading, so it is impossible now.
     * @see https://github.com/laravel/framework/issues/29658
     */
    protected function bootIfNotBooted(): void
    {
        if ($this->booted) {
            return;
        }

        if (! $this->translatable->relationLoaded('translations')) {
            return;
        }

        $this->loadTranslations($this->translatable->translations);

        $this->booted = true;
    }

    /**
     * Update existing translation on the model or create a new one if it is missing.
     */
    protected function updateOrCreateTranslation(string $attribute, string $locale, $value): void
    {
        $this->translatable->translations()->updateOrCreate([
            'translatable_attribute' => $attribute,
            'locale' => $locale,
        ], [
            'value' => $value,
        ]);
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

    /**
     * Load translation values from the given collection of translations.
     */
    protected function loadTranslations(Collection $translations): void
    {
        $translations->each(function (Translation $translation) {
            $this->translations[$translation->locale][$translation->translatable_attribute] = $translation->value;
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

    // TODO: feature deleting
    // /**
    //  * Delete translation from the model for the given attribute and locale.
    //  */
    // public function delete(string $attribute, string $locale)
    // {
    //     $this->model->translations()
    //         ->forAttribute($attribute)
    //         ->forLocale($locale)
    //         ->delete();
    // }
    //
    // /**
    //  * Delete all translations from the model for the given locale.
    //  */
    // public function deleteForLocale(string $locale = null)
    // {
    //     $this->model->translations()
    //         ->forLocale($locale)
    //         ->delete();
    // }
}
