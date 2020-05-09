<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Models\Translation;

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
     * ModelTranslator constructor.
     *
     * @param string $defaultLocale
     */
    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Set the current locale.
     *
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Get the translator locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale ?: $this->defaultLocale;
    }

    /**
     * Determine whether the translator uses the default locale as current locale.
     *
     * @return bool
     */
    public function isDefaultLocale(): bool
    {
        return $this->getLocale() === $this->defaultLocale;
    }

    /**
     * Get the translation of the given model.
     *
     * @param Model|HasTranslations $translatable
     * @param string $attribute
     * @param string $locale
     * @return mixed
     */
    public function get(Model $translatable, string $attribute, string $locale = null)
    {
        $locale = $locale ?: $this->getLocale();

        return $translatable->translations->filter(function (Translation $translation) use ($attribute, $locale) {
            return $translation->locale === $locale
                && $translation->translatable_attribute === $attribute;
        })->first()->value ?? null;
    }

    /**
     * Save the translation for the given model.
     *
     * @param Model|HasTranslations $translatable
     * @param string $attribute
     * @param string $value
     * @param string|null $locale
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
}
