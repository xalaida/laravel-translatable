<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    /**
     * The attributes that has loaded translation.
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

        return parent::getAttribute($attribute);
    }

    /**
     * Get translator.
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        return app(Translator::class);
    }

    /**
     * Get auto translator.
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
        $translation = $this->getTranslator()->get($this, $attribute);
        $this->translated[$attribute] = $translation ?: $this->attributes[$attribute];
        $this->attributes[$attribute] = $this->translated[$attribute];
    }
}
