<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;

class Translator
{
    /**
     * Determine if the application is set to the default locale.
     *
     * @return bool
     */
    public function isDefaultLocale(): bool
    {
        return app()->getLocale() === config('app.fallback_locale');
    }

    /**
     * Get translated attribute for the given model.
     *
     * @param Model|HasTranslations $translatable
     * @param string $attribute
     * @return mixed
     */
    public function get(Model $translatable, string $attribute)
    {
        return $translatable->translations->filter(function (Translation $translation) use ($attribute) {
            return $translation->locale === app()->getLocale()
                && $translation->translatable_attribute === $attribute;
        })->first()->translatable_value ?? null;
    }

    /**
     * Set translation for the given model.
     *
     * @param Model|HasTranslations $translatable
     * @param string $attribute
     * @param string $value
     * @param string|null $locale
     * @return Translation
     */
    public function set(Model $translatable, string $attribute, string $value, ?string $locale = null): Translation
    {
        return $translatable->translations()->create([
            'translatable_attribute' => $attribute,
            'translatable_value' => $value,
            'locale' => $locale ?: app()->getLocale(),
        ]);
    }
}
