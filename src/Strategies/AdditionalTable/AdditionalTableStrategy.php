<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTable;

use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Nevadskiy\Translatable\Strategies\AdditionalTable\Models\Translation;
use Nevadskiy\Translatable\Strategies\RelationTranslatorStrategy;

class AdditionalTableStrategy extends RelationTranslatorStrategy
{
    /**
     * The default mode class of the strategy.
     *
     * @var string
     */
    protected static $model = Translation::class;

    /**
     * Specify the translation model class.
     */
    public static function useModel(string $model): void
    {
        if (! is_a($model, Translation::class, true)) {
            throw new InvalidArgumentException(sprintf('A %s must extend the %s model.', $model, Translation::class));
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
