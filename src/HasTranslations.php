<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    /**
     * The attributes that have loaded translation.
     *
     * @var array
     */
    protected $translated = [];

    /**
     * Boot HasTranslations trait.
     */
    public static function bootHasTranslations(): void
    {
        static::addGlobalScope(new TranslationsEagerLoadScope());
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
     * Save translations for the attributes.
     *
     * @param array $translations
     * @param string $locale
     * @return void
     */
    public function translate(array $translations, string $locale = null): void
    {
        foreach ($translations as $attribute => $value) {
            $this->getTranslator()->set($this, $attribute, $value, $locale);
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

        return $this->withGetAttribute($attribute, $this->translated[$attribute]);
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
     * Get the translator.
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        return app(Translator::class);
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
     * Determine if the attribute should be translated.
     *
     * @param $attribute
     * @return bool
     */
    private function shouldBeTranslated(string $attribute): bool
    {
        return $this->isTranslatable($attribute)
            && ! $this->getTranslator()->isDefaultLocale();
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
    private function hasLoadedTranslation(string $attribute): bool
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
        $this->translated[$attribute] = $this->getTranslator()->get($this, $attribute) ?: $this->attributes[$attribute];
    }

    /**
     * Get the attribute value with all accessors and casts applied.
     *
     * @param string $attribute
     * @param string $value
     * @return mixed
     */
    private function withGetAttribute(string $attribute, string $value)
    {
        $original = $this->attributes[$attribute];

        $this->attributes[$attribute] = $value;

        $processed = parent::getAttribute($attribute);

        $this->attributes[$attribute] = $original;

        return $processed;
    }
}
