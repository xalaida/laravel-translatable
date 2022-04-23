<?php

namespace Nevadskiy\Translatable\Strategies;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Translator;

/**
 * @mixin Model
 */
trait InteractsWithTranslations
{
    /**
     * The model translator instance.
     */
    protected $translator;

    /**
     * Init the trait.
     */
    protected function initializeInteractsWithTranslations(): void
    {
        $this->translator = $this->newTranslator();
    }

    /**
     * Get the translator instance for the model.
     */
    public function translator(): Translator
    {
        return $this->translator;
    }

    /**
     * Make a new translator instance for the model.
     */
    public function newTranslator(): Translator
    {
        return new Translator($this, $this->getTranslationStrategy());
    }

    /**
     * Get the translation strategy.
     */
    abstract protected function getTranslationStrategy(): TranslatorStrategy;

    /**
     * Get an attribute from the model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        if (! $this->isTranslatable($attribute)) {
            return $this->getOriginalAttribute($attribute);
        }

        return $this->translator()->getOrFallback($attribute);
    }

    /**
     * TODO: get fallback translation
     * Get attribute's default value without translation.
     *
     * @return mixed
     */
    public function getOriginalAttribute(string $attribute)
    {
        return parent::getAttribute($attribute);
    }

    /**
     * Get the attribute value with all accessors and casts applied.
     *
     * @param mixed $value
     * @return mixed
     */
    public function withAttributeGetter(string $key, $value)
    {
        if (isset($this->attributes[$key])) {
            $original = $this->attributes[$key];
        }

        $this->attributes[$key] = $value;

        $processed = parent::getAttribute($key);

        if (isset($original)) {
            $this->attributes[$key] = $original;
        } else {
            unset($this->attributes[$key]);
        }

        return $processed;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($attribute, $value)
    {
        if (! $this->isTranslatable($attribute)) {
            return $this->setOriginalAttribute($attribute, $value);
        }

        $this->translator()->set($attribute, $value);

        return $this;
    }

    /**
     * Set attribute's value without translation.
     *
     * @param mixed $value
     * @return mixed
     */
    public function setOriginalAttribute(string $attribute, $value)
    {
        return parent::setAttribute($attribute, $value);
    }

    /**
     * Get the attribute value with all mutators and casts applied.
     *
     * @param mixed $value
     * @return mixed
     */
    public function withAttributeSetter(string $key, $value)
    {
        if (isset($this->attributes[$key])) {
            $original = $this->attributes[$key];
        }

        parent::setAttribute($key, $value);

        $processed = $this->attributes[$key];

        if (isset($original)) {
            $this->attributes[$key] = $original;
        } else {
            unset($this->attributes[$key]);
        }

        return $processed;
    }

    /**
     * Determine whether the attribute is translatable.
     */
    public function isTranslatable(string $attribute): bool
    {
        return collect($this->getTranslatable())->contains($attribute);
    }

    /**
     * Get translatable attributes.
     */
    public function getTranslatable(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Convert the model's attributes to an array.
     */
    public function attributesToArray(): array
    {
        return array_merge(parent::attributesToArray(), $this->translator()->toArray());
    }
}
