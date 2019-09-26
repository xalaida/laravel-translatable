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
     * @param string $attribute
     * @param Model|HasTranslations $translatable
     * @return mixed
     */
    public function get(string $attribute, Model $translatable)
    {
        return $translatable->translations->filter(function (Translation $translation) use ($attribute) {
            return $translation->locale === app()->getLocale()
                && $translation->translatable_attribute === $attribute;
        })->first()->translatable_value ?? null;
    }

    /**
     * @param string $attribute
     * @param string $locale
     * @return mixed
     */
    protected function getLoadedTranslation(string $attribute, $locale)
    {
        return $this->translatable->translations
                ->filter(function ($translation) use ($attribute, $locale) {
                    return $translation->isAttribute($attribute)
                        && $translation->isLocale($locale);
                })
                ->first()
                ->translatable_value ?? null;
    }
    /**
     * @param string $attribute
     * @param $locale
     * @return mixed
     */
    protected function loadTranslation(string $attribute, string $locale)
    {
        return $this->translatable
            ->translations()
            ->locale($locale)
            ->attribute($attribute)
            ->value('translatable_value');
    }
}
