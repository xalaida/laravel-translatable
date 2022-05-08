<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslator;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use Nevadskiy\Translatable\Events\TranslationMissing;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use function app;
use function event;

// TODO: cover test when fallback locale is missing (should throw an exception)
// TODO: add possibility to check if value is set (has method)
// TODO: consider adding unset method
class Translator
{
    /**
     * The translatable model instance.
     *
     * @var Model|InteractsWithTranslator
     */
    private $model;

    /**
     * The translator strategy instance.
     *
     * @var TranslatorStrategy
     */
    private $strategy;

    /**
     * Indicates whether the translator should use a fallback behaviour.
     *
     * @var bool
     */
    private $fallback = true;

    /**
     * The default locale.
     *
     * @var string
     */
    protected $fallbackLocale;

    /**
     * Make a new translator instance.
     */
    public function __construct(Model $model, TranslatorStrategy $strategy)
    {
        $this->model = $model;
        $this->strategy = $strategy;
        // TODO: keep it sync with resources' translator and make it octane friendly.
        $this->fallbackLocale = config('app.fallback_locale');
    }

    /**
     * Get the translator locale.
     */
    public function getLocale(): string
    {
        // TODO: refactor
        return app()->getLocale();
    }

    /**
     * Get the fallback locale.
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale;
    }

    /**
     * Set up the fallback locale for the translator.
     */
    public function fallbackLocale(string $locale): Translator
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Determine does the translator use the current or given locale as the default locale.
     */
    public function isFallbackLocale(string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return $locale === $this->getFallbackLocale();
    }

    /**
     * Translator will return null instead of a translation in the fallback locale when the translation is missing.
     */
    public function disableFallback(): Translator
    {
        $this->fallback = false;

        return $this;
    }

    /**
     * Determine whether the translator should return a fallback translation when the requested translation is missing.
     */
    public function shouldFallback(): bool
    {
        return $this->fallback;
    }

    /**
     * Set the translation for the model.
     */
    public function set(string $attribute, $value, string $locale = null): void
    {
        $this->assertAttributeIsTranslatable($attribute);

        $this->strategy->set(
            $attribute,
            $this->model->withAttributeSetter($attribute, $value),
            $locale ?: $this->getLocale()
        );
    }

    /**
     * Get the translation value of the given attribute or throw an exception.
     */
    public function getOrFail(string $attribute, string $locale = null)
    {
        return $this->model->withAttributeGetter($attribute, $this->getRawOrFail($attribute, $locale));
    }

    /**
     * Get the translation value of the given attribute or the default value if it is missing.
     *
     * @param callable|string $default
     */
    public function getOr(string $attribute, string $locale = null, $default = null)
    {
        try {
            return $this->getOrFail($attribute, $locale);
        } catch (TranslationMissingException $e) {
            event(new TranslationMissing($e->model, $e->attribute, $e->locale));

            return value($default);
        }
    }

    /**
     * Get the translation value of the given attribute to the given locale.
     */
    public function get(string $attribute, string $locale = null)
    {
        if ($this->shouldFallback()) {
            return $this->getOrFallback($attribute, $locale);
        }

        return $this->getOr($attribute, $locale);
    }

    /**
     * Get the translation value of the given attribute or the fallback value if it is missing.
     */
    public function getOrFallback(string $attribute, string $locale = null)
    {
        return $this->getOr($attribute, $locale, function () use ($attribute) {
            return $this->getFallback($attribute);
        });
    }

    /**
     * Get the fallback translation of the given attribute.
     */
    public function getFallback(string $attribute)
    {
        return $this->getOr($attribute, $this->getFallbackLocale());
    }

    /**
     * Get the raw translation value of the given attribute or throw an exception.
     */
    public function getRawOrFail(string $attribute, string $locale = null)
    {
        $this->assertAttributeIsTranslatable($attribute);

        return $this->strategy->get($attribute, $locale ?: $this->getLocale());
    }

    /**
     * Save the given translation for the model.
     */
    public function add(string $attribute, $value, string $locale = null): void
    {
        $this->set($attribute, $value, $locale);
        $this->model->save();
    }

    /**
     * Set many translations on the model for the given locale.
     */
    public function setMany(array $translations, string $locale = null): Translator
    {
        foreach ($translations as $attribute => $value) {
            $this->set($attribute, $value, $locale);
        }

        return $this;
    }

    /**
     * Add many translations to the model for the given locale.
     */
    public function addMany(array $translations, string $locale = null): void
    {
        $this->setMany($translations, $locale)->save();
    }

    /**
     * Save translations into the database.
     */
    public function save(): void
    {
        $this->strategy->save();
    }

    /**
     * Delete translation from the database.
     */
    public function delete(): void
    {
        $this->strategy->delete();
    }

    /**
     * Get list of translations for all translatable attributes for the given locale.
     */
    public function toArray(string $locale = null): array
    {
        $locale = $locale ?: $this->getLocale();

        $translations = [];

        foreach ($this->model->getTranslatable() as $attribute) {
            $translations[$attribute] = $this->get($attribute, $locale);
        }

        return $translations;
    }

    /**
     * Assert that the given attribute is translatable.
     */
    public function assertAttributeIsTranslatable(string $attribute): void
    {
        if (! $this->model->isTranslatable($attribute)) {
            throw new AttributeNotTranslatableException($attribute);
        }
    }
}
