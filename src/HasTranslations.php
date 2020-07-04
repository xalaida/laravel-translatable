<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nevadskiy\Translatable\Events\TranslationNotFoundEvent;
use Nevadskiy\Translatable\Exceptions\NotTranslatableAttributeException;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Scopes\TranslationsEagerLoadScope;

/**
 * @mixin Model
 * @property Translation[] translations
 */
trait HasTranslations
{
    use TranslationScopes,
        TranslatableUrlRouting;

    /**
     * The attributes that have loaded translation.
     *
     * @var array
     */
    protected $translated = [];

    /**
     * Boot the trait.
     */
    protected static function bootHasTranslations(): void
    {
        static::addGlobalScope(new TranslationsEagerLoadScope());

        static::saving(static function (self $translatable) {
            $translatable->handleSavingEvent();
        });

        static::deleted(static function (self $translatable) {
            $translatable->handleDeletedEvent();
        });
    }

    /**
     * Morph many translations relation.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
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

        return $this->getTranslationOrDefault($attribute);
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
            return $this->setDefaultAttribute($attribute, $value);
        }

        return $this->setTranslation($attribute, $value);
    }

    /**
     * Get attribute's default value without translation.
     *
     * @return mixed
     */
    public function getDefaultAttribute(string $attribute)
    {
        return parent::getAttribute($attribute);
    }

    /**
     * Set attribute's value without translation.
     *
     * @param mixed $value
     * @return mixed
     */
    public function setDefaultAttribute(string $attribute, $value)
    {
        return parent::setAttribute($attribute, $value);
    }

    /**
     * Save translation for the given attribute and locale.
     *
     * @param mixed $value
     * @return HasTranslations|mixed
     */
    public function translate(string $attribute, $value, string $locale)
    {
        $this->setTranslation($attribute, $value, $locale)->save();

        return $this;
    }

    /**
     * Save many translations for the given attribute and locale.
     *
     * @return HasTranslations|mixed
     */
    public function translateMany(array $translations, string $locale)
    {
        foreach ($translations as $attribute => $value) {
            $this->setTranslation($attribute, $value, $locale);
        }

        $this->save();

        return $this;
    }

    /**
     * Get translation value for the attribute.
     *
     * @return mixed
     */
    public function getTranslation(string $attribute, string $locale = null)
    {
        $this->assertTranslatableAttribute($attribute);

        $locale = $locale ?: static::getTranslator()->getLocale();

        if (static::getTranslator()->isDefaultLocale($locale)) {
            return $this->getDefaultAttribute($attribute);
        }

        $rawTranslation = $this->getRawTranslation($attribute, $locale);

        if (is_null($rawTranslation)) {
            return null;
        }

        return $this->withGetAttribute($attribute, $rawTranslation);
    }

    /**
     * Get raw translation value for the attribute.
     *
     * @return mixed
     */
    public function getRawTranslation(string $attribute, string $locale = null)
    {
        $locale = $locale ?: static::getTranslator()->getLocale();

        if (! $this->hasLoadedTranslation($attribute, $locale)) {
            $this->loadTranslation($attribute, $locale);
        }

        $translation = $this->getLoadedTranslation($attribute, $locale);

        if (is_null($translation)) {
            event(new TranslationNotFoundEvent($this, $attribute, $locale));
        }

        return $translation;
    }

    /**
     * Determine whether the attribute has loaded translation.
     */
    protected function hasLoadedTranslation(string $attribute, string $locale): bool
    {
        return isset($this->translated[$locale][$attribute]);
    }

    /**
     * Load the attribute translation.
     */
    protected function loadTranslation(string $attribute, string $locale): void
    {
        $this->translated[$locale][$attribute] = static::getTranslator()->get($this, $attribute, $locale);
    }

    /**
     * Get the loaded attribute translation.
     *
     * @return mixed
     */
    protected function getLoadedTranslation(string $attribute, string $locale)
    {
        return $this->translated[$locale][$attribute];
    }

    /**
     * Set translation to the attribute.
     *
     * @param mixed $value
     * @return HasTranslations|mixed
     */
    public function setTranslation(string $attribute, $value, string $locale = null)
    {
        $this->assertTranslatableAttribute($attribute);

        $locale = $locale ?: static::getTranslator()->getLocale();

        if (static::getTranslator()->isDefaultLocale($locale)) {
            return $this->setDefaultAttribute($attribute, $value);
        }

        $this->translated[$locale][$attribute] = $this->withSetAttribute($attribute, $value);

        return $this;
    }

    /**
     * Determine whether the attribute should be translated.
     */
    protected function shouldBeTranslated(string $attribute): bool
    {
        return $this->exists && $this->isTranslatable($attribute);
    }

    /**
     * Determine whether the attribute is translatable.
     */
    protected function isTranslatable(string $attribute): bool
    {
        return in_array($attribute, $this->getTranslatable(), true);
    }

    /**
     * Get translatable attributes.
     */
    public function getTranslatable(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Handle the model saving event.
     */
    protected function handleSavingEvent(): void
    {
        $this->saveTranslations();
    }

    /**
     * Save the model translations.
     */
    protected function saveTranslations(): void
    {
        foreach ($this->translated as $locale => $attributes) {
            foreach (array_filter($attributes) as $attribute => $value) {
                static::getTranslator()->set($this, $attribute, $value, $locale);
            }
        }
    }

    /**
     * Handle the model deleted event.
     */
    protected function handleDeletedEvent(): void
    {
        if ($this->shouldDeleteTranslations()) {
            $this->deleteTranslations();
        }
    }

    /**
     * Determine whether the model should delete translations.
     */
    protected function shouldDeleteTranslations(): bool
    {
        if (! $this->isUsingSoftDeletes()) {
            return true;
        }

        if ($this->isForceDeleting()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the model uses soft deletes.
     */
    protected function isUsingSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses($this), true);
    }

    /**
     * Delete the model translations.
     */
    protected function deleteTranslations(): void
    {
        $this->translations()->delete();
    }

    /**
     * Get a translation of the attribute or default value if translation is missing.
     *
     * @return mixed
     */
    public function getTranslationOrDefault(string $attribute, string $locale = null)
    {
        $translation = $this->getTranslation($attribute, $locale);

        if (is_null($translation)) {
            return $this->getDefaultAttribute($attribute);
        }

        return $translation;
    }

    /**
     * Get the attribute value with all accessors and casts applied.
     *
     * @param mixed $value
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
     * @param mixed $value
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
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), array_filter($this->getTranslations()));
    }

    /**
     * Get model translations.
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
     * Get the model translator instance.
     */
    protected static function getTranslator(): ModelTranslator
    {
        return app(ModelTranslator::class);
    }

    /**
     * Assert that attribute is translatable.
     */
    protected function assertTranslatableAttribute(string $attribute): void
    {
        if (! $this->isTranslatable($attribute)) {
            throw NotTranslatableAttributeException::fromAttribute($attribute);
        }
    }
}
