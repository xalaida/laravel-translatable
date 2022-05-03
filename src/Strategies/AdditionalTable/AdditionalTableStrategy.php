<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTable;

use Illuminate\Database\Eloquent\Model;
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
    private $model;

    /**
     * A list of pending translation insertions.
     *
     * @var array
     */
    private $pendingTranslations = [];

    /**
     * Make a new strategy instance.
     *
     * @param Model|HasTranslations $model
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

        return $this->getFromRelation($locale, $attribute);
    }

    /**
     * @inheritdoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        $this->pendingTranslations[$locale][$attribute] = $value;
    }

    /**
     * Determine if the given locale is fallback locale.
     */
    private function isFallbackLocale(string $locale): bool
    {
        return $locale === 'en';
    }

    public function save(): void
    {
        foreach ($this->pullPendingTranslations() as $locale => $attributes) {
            $this->model->translations()->updateOrCreate(['locale' => $locale], $attributes);
        }
    }

    private function pullPendingTranslations(): array
    {
        $pendingTranslations = $this->pendingTranslations;

        $this->pendingTranslations = [];

        return $pendingTranslations;
    }

    /**
     * Find the translation value in the translations' relation.
     */
    private function getFromRelation(string $locale, string $attribute)
    {
        $translation = $this->model->translations->first(function (Translation $translation) use ($locale) {
            return $translation->locale === $locale;
        });

        if (! $translation) {
            return null;
        }

        return $translation->getAttribute($attribute);
    }

    public function delete(): void
    {
        // TODO: Implement delete() method.
    }
}
