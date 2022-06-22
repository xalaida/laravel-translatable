<?php

namespace Nevadskiy\Translatable\Strategies\SingleTableExtended;

use Nevadskiy\Translatable\Strategies\SingleTable\SingleTableStrategy;

class SingleTableExtendedStrategy extends SingleTableStrategy
{
    /**
     * @inheritdoc
     */
    public function get(string $attribute, string $locale)
    {
        $this->bootIfNotBooted();

        if ($this->translatable->translator()->isFallbackLocale($locale)) {
            return $this->translatable->getRawAttribute($attribute);
        }

        return parent::get($attribute, $locale);
    }

    /**
     * @inheritdoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        if ($this->translatable->translator()->isFallbackLocale($locale)) {
            $this->translatable->setRawAttribute($attribute, $value);
        } else {
            parent::set($attribute, $value, $locale);
        }
    }

    /**
     * @inheritdoc
     */
    public function getLocalesForEagerLoading(): array
    {
        $locales = [];

        if (! $this->translatable->translator()->isFallbackLocale()) {
            $locales[] = $this->translatable->translator()->getLocale();
        }

        return $locales;
    }
}
