<?php

namespace Nevadskiy\Translatable\Strategies;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;
use Nevadskiy\Translatable\Models\EntityTranslation;

class AdditionalTableStrategy implements TranslatorStrategy
{
    /**
     * The translatable model instance.
     *
     * @var Model
     */
    private $model;

    /**
     * Make a new strategy instance.
     *
     * @param Model|HasTranslations $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // TODO: add possibility to use default values...
    // TODO: add possibility configure where to put default/fallback locale values (own/original table, additional/translations table)

    /**
     * Get the translation value from the collection of translations.
     */
    public function get(string $attribute, string $locale)
    {
        $translation = $this->model->translations->where('locale', $locale)->first();

        if (! $translation) {
            return null;
        }

        return $translation->getAttribute($attribute);
    }

    /**
     * Set the translation value for the given attribute with the given locale.
     *
     * @param mixed $value
     */
    public function set(string $attribute, $value, string $locale): EntityTranslation
    {
        // TODO: possible 'nullable' insert error case here for multiple fields (we setting translation for only one field but actually required two).
        return $this->model->translations()->updateOrCreate([
            'locale' => $locale
        ], [
            $attribute => $value
        ]);
    }
}
