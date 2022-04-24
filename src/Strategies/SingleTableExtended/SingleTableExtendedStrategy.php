<?php

namespace Nevadskiy\Translatable\Strategies\SingleTableExtended;

use Nevadskiy\Translatable\Strategies\SingleTable\SingleTableStrategy;

/**
 * TODO: add possibility to trigger an exception when creating model in non-default locale.
 */
class SingleTableExtendedStrategy extends SingleTableStrategy
{
    /**
     * @inheritdoc
     */
    public function get(string $attribute, string $locale)
    {
        if ($this->shouldGetFromOriginalAttribute($locale)) {
            return $this->model->getRawOriginal($attribute);
        }

        return parent::get($attribute, $locale);
    }

    /**
     * @inheritdoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        if ($this->shouldSetToOriginalAttribute($locale)) {
            $this->model->setRawOriginal($attribute, $value);
        } else {
            parent::set($attribute, $value, $locale);
        }
    }

    /**
     * Determine if the translation should be retrieved from the original attribute.
     */
    protected function shouldGetFromOriginalAttribute(string $locale): bool
    {
        return $this->model->translator()->isFallbackLocale($locale);
    }

    /**
     * Determine if the translation should be set to the original attribute.
     */
    protected function shouldSetToOriginalAttribute(string $locale): bool
    {
        if (! $this->model->exists) {
            return true;
        }

        return $this->model->translator()->isFallbackLocale($locale);
    }
}
