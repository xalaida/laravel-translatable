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
     * @TODO: add description. (probably just extract into separate strategy to provide simpler approach with trait and only related scoped)
     */
    private $copyingStructure = true;

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
        if ($this->shouldGetFromOriginalAttribute($locale)) {
            return $this->model->getOriginalAttribute($attribute);
        }

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
        if ($this->shouldSetAsOriginalAttribute($locale)) {
            $this->model->setOriginalAttribute($attribute, $value);
        } else {
            $this->pendingTranslations[$locale][$attribute] = $this->model->withAttributeSetter($attribute, $value);
        }
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
        // TODO: possible 'nullable' insert error case here for multiple fields (we setting translation for only one field but actually required two).
        foreach ($this->pullPendingTranslations() as $locale => $attributes) {
            $this->model->translations()->updateOrCreate(['locale' => $locale], $attributes);
        }

//        // TODO: make this configurable OR probably remove completely that feature (think what if only fallback locale attributes are translated? its broken here)
//        if ($translations) {
//            $this->model->touch();
//        }
    }

    private function pullPendingTranslations(): array
    {
        $pendingTranslations = $this->pendingTranslations;

        $this->pendingTranslations = [];

        return $pendingTranslations;
    }

    public function copyingTranslatableStructure()
    {
        $this->copyingStructure = true;

        return $this;
    }

    public function extendingTranslatableStructure()
    {
        $this->copyingStructure = false;

        return $this;
    }

    /**
     * @param string $locale
     * @return bool
     */
    private function shouldSetAsOriginalAttribute(string $locale): bool
    {
        if (! $this->copyingStructure) {
            return false;
        }

        if (! $this->model->exists) {
            return true;
        }

        return $this->isFallbackLocale($locale);
    }

    private function shouldGetFromOriginalAttribute(string $locale): bool
    {
        if (! $this->copyingStructure) {
            return false;
        }

        return $this->isFallbackLocale($locale);
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
}
