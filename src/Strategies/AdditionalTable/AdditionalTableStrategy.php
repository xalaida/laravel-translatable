<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTable;

use Illuminate\Database\Eloquent\Collection;
use Nevadskiy\Translatable\Strategies\AdditionalTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\RelationTranslatorStrategy;

class AdditionalTableStrategy extends RelationTranslatorStrategy
{
    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    protected function saveTranslations(array $translations): void
    {
        foreach ($translations as $locale => $attributes) {
            $this->translatable->translations()->updateOrCreate(['locale' => $locale], $attributes);
        }
    }
}
