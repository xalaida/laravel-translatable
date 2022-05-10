<?php

namespace Nevadskiy\Translatable\Strategies\AdditionalTableExtended;

use Nevadskiy\Translatable\Strategies\AdditionalTable\AdditionalTableStrategy;

class AdditionalTableExtendedStrategy extends AdditionalTableStrategy
{
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
}
