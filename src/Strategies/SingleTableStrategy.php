<?php

namespace Nevadskiy\Translatable\Strategies;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;
use Nevadskiy\Translatable\Models\Translation;

class SingleTableStrategy implements TranslatorStrategy
{
    /**
     * The translatable model instance.
     *
     * @var Model|HasTranslations
     */
    private $model;

    /**
     * Make a new strategy instance.
     *
     * TODO: probably swap Model with 'Translatable' interface to decouple dependencies.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function get(string $attribute, string $locale)
    {
        // TODO Reduce the amount of fields using partition selects.
        return $this->model->translations->first(static function (Translation $translation) use ($attribute, $locale) {
            return $translation->translatable_attribute === $attribute
                && $translation->locale === $locale;
        })->value ?? null;
    }

    public function set(string $attribute, $value, string $locale)
    {
        return $this->model->translations()->updateOrCreate([
            'translatable_attribute' => $attribute,
            'locale' => $locale,
        ], [
            'value' => $value,
        ]);
    }
}
