<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Nevadskiy\Translatable\Events\TranslationNotFoundEvent;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Scopes\TranslationsEagerLoadScope;

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
        static::addGlobalScope(new TranslationsEagerLoadScope);

        static::saving(static function (self $translatable) {
            $translatable->handleSavingEvent();
        });
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
     * Save translation for the given attribute and locale.
     *
     * @param string $attribute
     * @param $value
     * @param string $locale
     * @return Translation
     */
    public function translate(string $attribute, $value, string $locale): Translation
    {
        return static::getTranslator()->set($this, $attribute, $this->withSetAttribute($attribute, $value), $locale);
    }

    /**
     * Save many translations for the given attribute and locale.
     *
     * @param array $translations
     * @param string $locale
     * @return Collection
     */
    public function translateMany(array $translations, string $locale): Collection
    {
        $collectionsCollection = new Collection();

        foreach ($translations as $attribute => $value) {
            $collectionsCollection[] = $this->translate($attribute, $value, $locale);
        }

        return $collectionsCollection;
    }

    /**
     * Get translation value for the attribute.
     *
     * @param string $attribute
     * @param string|null $locale
     * @return mixed
     */
    public function getTranslation(string $attribute, string $locale = null)
    {
        $locale = $locale ?: static::getTranslator()->getLocale();

        $translation = $this->getRawTranslation($attribute, $locale);

        if (is_null($translation)) {
            event(new TranslationNotFoundEvent($this, $attribute, $locale));

            return null;
        }

        return $this->withGetAttribute($attribute, $translation);
    }

    /**
     * Get raw translation value for the attribute.
     *
     * @param string $attribute
     * @param string|null $locale
     * @return mixed
     */
    public function getRawTranslation(string $attribute, string $locale = null)
    {
        $locale = $locale ?: static::getTranslator()->getLocale();

        if (! $this->hasLoadedTranslation($attribute, $locale)) {
            $this->loadTranslation($attribute, $locale);
        }

        return $this->getLoadedTranslation($attribute, $locale);
    }

    /**
     * Get model translations.
     *
     * @param string|null $locale
     * @return array
     */
    public function getTranslations(string $locale = null): array
    {
        $locale = $locale ?: static::getTranslator()->getLocale();

        $translations = [];

        foreach ($this->translatable as $attribute) {
            $translations[$attribute] = $this->getTranslation($attribute, $locale);
        }

        return $translations;
    }

    /**
     * Get attribute's default value without translation.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getDefaultAttribute(string $attribute)
    {
        return parent::getAttribute($attribute);
    }

    /**
     * Determine whether the attribute has loaded translation.
     *
     * @param string $attribute
     * @param string $locale
     * @return bool
     */
    protected function hasLoadedTranslation(string $attribute, string $locale): bool
    {
        return isset($this->translated[$locale][$attribute]);
    }

    /**
     * Load the attribute translation.
     *
     * @param string $attribute
     * @param string $locale
     */
    protected function loadTranslation(string $attribute, string $locale): void
    {
        $this->translated[$locale][$attribute] = static::getTranslator()->get($this, $attribute, $locale);
    }

    /**
     * Get the loaded attribute translation.
     *
     * @param string $attribute
     * @param string $locale
     * @return mixed
     */
    protected function getLoadedTranslation(string $attribute, string $locale)
    {
        return $this->translated[$locale][$attribute];
    }

    /**
     * Set translation to the attribute.
     *
     * @param string $attribute
     * @param $value
     * @param string|null $locale
     */
    private function setTranslation(string $attribute, $value, string $locale = null): void
    {
        $locale = $locale ?: static::getTranslator()->getLocale();

        $this->translated[$locale][$attribute] = $this->withSetAttribute($attribute, $value);
    }

    /**
     * Determine whether the attribute should be translated.
     *
     * @param $attribute
     * @return bool
     */
    protected function shouldBeTranslated(string $attribute): bool
    {
        return $this->exists
            && $this->isTranslatable($attribute)
            && ! static::getTranslator()->isDefaultLocale();
    }

    /**
     * Determine whether the attribute is translatable.
     *
     * @param string $attribute
     * @return bool
     */
    protected function isTranslatable(string $attribute): bool
    {
        return in_array($attribute, $this->getTranslatable(), true);
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
     * On saving event listener.
     */
    public function handleSavingEvent(): void
    {
        $this->saveTranslations();
    }

    /**
     * Save the model translations.
     */
    public function saveTranslations(): void
    {
        foreach ($this->translated as $locale => $attributes) {
            $this->translateMany(array_filter($attributes), $locale);
        }
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        if (! $this->shouldBeTranslated($attribute)) {
            return $this->getDefaultAttribute($attribute);
        }

        $translation = $this->getTranslation($attribute);

        if (is_null($translation)) {
            return $this->getDefaultAttribute($attribute);
        }

        return $translation;
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
        if (! $this->shouldBeTranslated($attribute)) {
            return parent::setAttribute($attribute, $value);
        }

        $this->setTranslation($attribute, $value);

        return $this;
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
     * Get the attribute value with all mutators and casts applied.
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
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), array_filter($this->getTranslations()));
    }

    /**
     * Get the model translator instance.
     *
     * @return ModelTranslator
     */
    public static function getTranslator(): ModelTranslator
    {
        return app(ModelTranslator::class);
    }
}
