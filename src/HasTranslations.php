<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 * @property Translation[] translations
 */
trait HasTranslations
{
    /**
     * The attributes that have loaded translation.
     *
     * @var array
     */
    protected $translated = [];

    /**
     * Boot the trait.
     */
    public static function bootHasTranslations(): void
    {
        static::addGlobalScope(new TranslationsEagerLoadScope());

        static::saving(function (self $translatable) {
            $translatable->onSavingEvent();
        });
    }

    /**
     * Get translatable attributes.
     *
     * @return array
     */
    public function getTranslatable(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Morph many translations relation.
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * On saving event listener.
     */
    public function onSavingEvent(): void
    {
        $this->translate(array_filter($this->translated));
    }

    /**
     * Save translations for the attributes.
     *
     * @param array $translations
     * @param string $locale
     * @return void
     */
    public function translate(array $translations, string $locale = null): void
    {
        foreach ($translations as $attribute => $value) {
            static::getTranslator()->set($this, $attribute, $this->withSetAttribute($attribute, $value), $locale);
        }
    }

    /**
     * Translate model attributes using translator engine.
     *
     * @param string $locale
     * @return void
     */
    public function translateUsingEngine(string $locale): void
    {
        $this->getAutoTranslator()->translate($this, $locale);
    }

    /**
     * Get the attribute value.
     *
     * @param $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        if (! $this->shouldBeTranslated($attribute)) {
            return parent::getAttribute($attribute);
        }

        if (! $this->hasLoadedTranslation($attribute)) {
            $this->loadTranslation($attribute);
        }

        if (null === $this->translated[$attribute]) {
            return parent::getAttribute($attribute);
        }

        return $this->withGetAttribute($attribute, $this->translated[$attribute]);
    }

    /**
     * Set the attribute value.
     *
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function setAttribute($attribute, $value)
    {
        if (! $this->shouldBeTranslated($attribute)) {
            return parent::setAttribute($attribute, $value);
        }

        $this->translated[$attribute] = $this->withSetAttribute($attribute, $value);

        return $this;
    }

    /**
     * Get the attribute value without translation.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getWithoutTranslation(string $attribute)
    {
        return parent::getAttribute($attribute);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $translatedAttributes = [];

        foreach ($this->translatable as $attribute) {
            $translatedAttributes[$attribute] = $this->getAttribute($attribute);
        }

        return array_merge(parent::toArray(), $translatedAttributes);
    }

    /**
     * Get the auto translator.
     *
     * @return AutoTranslator
     */
    public function getAutoTranslator(): AutoTranslator
    {
        return app(AutoTranslator::class);
    }

    /**
     * Determine whether the attribute should be translated.
     *
     * @param $attribute
     * @return bool
     */
    protected function shouldBeTranslated(string $attribute): bool
    {
        return $this->isTranslatable($attribute)
            && ! static::getTranslator()->isDefaultLocale();
    }

    /**
     * Determine if the attribute is translatable.
     *
     * @param string $attribute
     * @return bool
     */
    protected function isTranslatable(string $attribute): bool
    {
        return in_array($attribute, $this->getTranslatable(), true);
    }

    /**
     * Determine if the attribute has loaded translation.
     *
     * @param $attribute
     * @return bool
     */
    public function hasLoadedTranslation(string $attribute): bool
    {
        return isset($this->translated[$attribute]);
    }

    /**
     * Load the attribute translation.
     *
     * @param string $attribute
     */
    protected function loadTranslation(string $attribute): void
    {
        $this->translated[$attribute] = static::getTranslator()->get($this, $attribute);
    }

    /**
     * Get the attribute value with all accessors and casts applied.
     *
     * @param string $attribute
     * @param $value
     * @return mixed
     */
    protected function withGetAttribute(string $attribute, $value)
    {
        $original = $this->attributes[$attribute];

        $this->attributes[$attribute] = $value;

        $processed = parent::getAttribute($attribute);

        $this->attributes[$attribute] = $original;

        return $processed;
    }

    /**
     * Get the attribute value with all mutators applied.
     *
     * @param string $attribute
     * @param $value
     * @return mixed
     */
    protected function withSetAttribute(string $attribute, $value)
    {
        $original = $this->attributes[$attribute];

        parent::setAttribute($attribute, $value);

        $processed = $this->attributes[$attribute];

        $this->attributes[$attribute] = $original;

        return $processed;
    }

    /**
     * Get the translator instance.
     *
     * @return Translator
     */
    public static function getTranslator(): Translator
    {
        return app(Translator::class);
    }
}
