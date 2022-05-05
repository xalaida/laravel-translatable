<?php

namespace Nevadskiy\Translatable\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Nevadskiy\Translatable\Strategies\RelationTranslatorStrategy;
use Nevadskiy\Translatable\Strategies\SingleTable\Models\Translation;

/**
 * TODO: add possibility to trigger an exception when creating model in non-default locale.
 */
class SingleTableExtendedStrategy extends RelationTranslatorStrategy
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
     * @inheritdoc
     */
    public function get(string $attribute, string $locale)
    {
        $this->bootIfNotBooted();

        if ($this->translatable->translator()->isFallbackLocale($locale)) {
            return $this->translatable->getRawOriginal($attribute);
        }

        return parent::get($attribute, $locale);
    }

    /**
     * @inheritdoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        if ($this->translatable->translator()->isFallbackLocale($locale)) {
            $this->translatable->setRawOriginal($attribute, $value);
        } else {
            parent::set($attribute, $value, $locale);
        }
    }

    /**
     * @inheritdoc
     */
    protected function loadTranslations(Collection $translations): void
    {
        $translations->each(function (Translation $translation) {
            $this->translations[$translation->locale][$translation->translatable_attribute] = $translation->value;
        });
    }

    /**
     * @inheritdoc
     */
    protected function saveTranslations(array $translations): void
    {
        foreach ($translations as $locale => $attributes) {
            foreach ($attributes as $attribute => $value) {
                $this->updateOrCreateTranslation($attribute, $locale, $value);
            }
        }
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
}
