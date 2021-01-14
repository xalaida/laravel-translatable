<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
     * Get the translation of the given model.
     *
     * @param Model|HasTranslations $translatable
     * @return mixed
     */
    public function get(Model $translatable, string $attribute, string $locale = null)
    {
        $locale = $locale ?: $this->getLocale();

        foreach ($translatable->translations as $translation) {
            if ($translation->translatable_attribute === $attribute && $translation->locale === $locale) {
                return $translation->value;
            }
        }

        return null;
    }

    /**
     * Save the given translations for the given model.
     *
     * @param Model|HasTranslations $model
     * @param array $translations
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
     * Save the translation for the given model.
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
            'id' => Str::uuid()->toString(),
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
        string $locale = null,
        bool $isPreferred = true
    ): Translation {
        return $translatable->translations()->firstOrCreate([
            'translatable_attribute' => $attribute,
            'locale' => $locale ?: $this->getLocale(),
            'value' => $value,
            'is_preferred' => $isPreferred,
        ]);
    }
}
