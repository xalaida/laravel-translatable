<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Models\Translation;

/**
 * TODO: replace with Translator class.
 *
 * @deprecated
 */
class ModelTranslator
{
    /**
     * The default locale.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * The current locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * Make a new translator instance.
     */
    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Set the current locale.
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Get the translator locale.
     */
    public function getLocale(): string
    {
        return $this->locale ?: $this->defaultLocale;
    }

    /**
     * Determine does the translator use the current or given locale as the default locale.
     */
    public function isDefaultLocale(string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return $locale === $this->defaultLocale;
    }

    /**
     * Save the given translations for the given model.
     *
     * @param Model|HasTranslations $model
     */
    public function save(Model $model, array $translations): void
    {
        foreach ($translations as $locale => $attributes) {
            foreach (array_filter($attributes) as $attribute => $value) {
                $this->set($model, $attribute, $value, $locale);
            }
        }
    }

    /**
     * Get the translation of the given model.
     *
     * @param Model|HasTranslations $translatable
     * @return mixed
     */
    public function get(Model $translatable, string $attribute, string $locale = null)
    {
        $locale = $locale ?: $this->getLocale();

        return $translatable->translations->first(static function (Translation $translation) use ($attribute, $locale) {
            return $translation->translatable_attribute === $attribute
                && $translation->locale === $locale;
        })->value ?? null;
    }

    /**
     * Set the translation for the given model.
     *
     * @param Model|HasTranslations $translatable
     * @return Translation|Model
     */
    public function set(Model $translatable, string $attribute, string $value, string $locale = null): Translation
    {
        return $translatable->translations()->updateOrCreate([
            'translatable_attribute' => $attribute,
            'locale' => $locale ?: $this->getLocale(),
        ], [
            'value' => $value,
        ]);
    }

    /**
     * Add a new translation for the given model.
     *
     * @param Model|HasTranslations $translatable
     */
    public function add(
        Model $translatable,
        string $attribute,
        string $value,
        ?string $locale
    ): Translation {
        return $translatable->translations()->firstOrCreate([
            'translatable_attribute' => $attribute,
            'locale' => $locale,
            'value' => $value,
        ]);
    }
}
