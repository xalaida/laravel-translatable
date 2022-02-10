<?php

namespace Nevadskiy\Translatable\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;
use Nevadskiy\Translatable\Models\Translation;

class TranslationFactory
{
    /**
     * Model attributes.
     */
    private $attributes = [];

    /**
     * Make a new factory instance.
     */
    public static function new(): self
    {
        return new static();
    }

    /**
     * Create a new model instance and save it into the database.
     */
    public function create(array $attributes = []): Translation
    {
        $translation = new Translation();
        $translation->forceFill(array_merge($this->getDefaults(), $this->attributes, $attributes));
        $translation->save();

        return $translation;
    }

    /**
     * Create a translation for the given model.
     *
     * @param Model|HasTranslations $model
     */
    public function for(Model $model, string $attribute = null): self
    {
        $this->attributes = array_merge($this->attributes, array_filter([
            'translatable_id' => $model->getKey(),
            'translatable_type' => $model->getMorphClass(),
            'translatable_attribute' => $attribute,
        ]));

        return $this;
    }

    /**
     * Create a translation for the given locale.
     */
    public function locale(string $locale): self
    {
        $this->attributes = array_merge($this->attributes, [
            'locale' => $locale,
        ]);

        return $this;
    }

    /**
     * Get default values.
     */
    private function getDefaults(): array
    {
        return [
            'locale' => 'la',
            'value' => 'lorem',
        ];
    }
}
