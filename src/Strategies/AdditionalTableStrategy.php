<?php

namespace Nevadskiy\Translatable\Strategies;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        // TODO: possible 'nullable' insert error case here for multiple fields (we setting translation for only one field but actually required two).
        $this->model->translations()->updateOrCreate([
            'locale' => $locale
        ], [
            $attribute => $value
        ]);
    }
}
