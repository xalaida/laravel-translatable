<?php

namespace Nevadskiy\Translatable\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * TODO: add possibility to trigger an exception when creating model in non-default locale.
 * TODO: add possibility to extract translatable attributes out of the model into single table (allows to create models in custom locale)
 * TODO: structure translations on 'retrieve' event (only when it was fired AFTER eager loading, not before like now)
 * TODO: structure loaded translation in the strategy as this: ['en' => ['title' => null], 'uk' => ['title' => 'Книга']]
 */
class SingleTableStrategy implements TranslatorStrategy
{
    // TODO: boot translation similar how laravel model is doing this (original and translations array and dirty on save)

    /**
     * The translatable model instance.
     *
     * @var Model|HasTranslations
     */
    protected $model;

    /**
     * A list of pending translation insertions.
     *
     * @var array
     */
    protected $pendingTranslations = [];

    /**
     * Make a new strategy instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function get(string $attribute, string $locale)
    {
        if (isset($this->pendingTranslations[$locale][$attribute])) {
            return $this->pendingTranslations[$locale][$attribute];
        }

        return $this->getFromRelation($attribute, $locale);
    }

    /**
     * @inheritdoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        $this->pendingTranslations[$locale][$attribute] = $value;
    }

    /**
     * Save the pending translations on the model.
     * TODO: should be protected and set up from the boot method.
     */
    public function save(): void
    {
        // TODO: assert that model exists in the database.

        foreach ($this->pullPendingTranslations() as $locale => $attributes) {
            foreach ($attributes as $attribute => $value) {
                $this->updateOrCreateTranslation($attribute, $locale, $value);
            }
        }
    }

    protected function pullPendingTranslations(): array
    {
        $pendingTranslations = $this->pendingTranslations;

        $this->pendingTranslations = [];

        return $pendingTranslations;
    }

    /**
     * @param string $attribute
     * @param string $locale
     * @return mixed
     */
    protected function getFromRelation(string $attribute, string $locale)
    {
        $translation = $this->model->translations->first(function (Translation $translation) use ($attribute, $locale) {
            return $translation->translatable_attribute === $attribute
                && $translation->locale === $locale;
        });

        if (! $translation) {
            throw new TranslationMissingException($this->model, $attribute, $locale);
        }

        return $translation->getAttribute('value');
    }

    /**
     * Update existing translation on the model or create a new one if it is missing.
     */
    protected function updateOrCreateTranslation(string $attribute, string $locale, $value): void
    {
        $this->model->translations()->updateOrCreate([
            'translatable_attribute' => $attribute,
            'locale' => $locale,
        ], [
            'value' => $value,
        ]);
    }

    // TODO: feature deleting
//    /**
//     * Delete translation from the model for the given attribute and locale.
//     */
//    public function delete(string $attribute, string $locale)
//    {
//        $this->model->translations()
//            ->forAttribute($attribute)
//            ->forLocale($locale)
//            ->delete();
//    }
//
//    /**
//     * Delete all translations from the model for the given locale.
//     */
//    public function deleteForLocale(string $locale = null)
//    {
//        $this->model->translations()
//            ->forLocale($locale)
//            ->delete();
//    }
//
//    /**
//     * Delete all translations from the model.
//     */
//    public function deleteAll(): void
//    {
//        $this->model->translations()->delete();
//    }
}
