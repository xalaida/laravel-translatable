<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslations;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use Nevadskiy\Translatable\Events\TranslationMissing;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use function app;
use function event;

class Translator
{
    /**
     * The translatable model instance.
     *
     * @var Model|InteractsWithTranslations
     */
    private $model;

    /**
     * The translator strategy instance.
     *
     * @var TranslatorStrategy
     */
    private $strategy;

    /**
     * The default locale.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * Make a new translator instance.
     */
    public function __construct(Model $model, TranslatorStrategy $strategy)
    {
        $this->model = $model;
        $this->strategy = $strategy;
        // TODO: maybe rename into 'fallback' locale (as laravel config name).
        $this->defaultLocale = 'en';
    }

    /**
     * Get the translator locale.
     */
    public function getLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Determine does the translator use the current or given locale as the default locale.
     */
    public function isFallbackLocale(string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return $locale === $this->defaultLocale;
    }

    /**
     * Set the translation for the model.
     */
    public function set(string $attribute, $value, string $locale = null): Translator
    {
        $this->assertAttributeIsTranslatable($attribute);

        $this->strategy->set($attribute, $value, $locale ?: $this->getLocale());

        return $this;
    }

    public function add(string $attribute, $value, string $locale = null): void
    {
        $this->set($attribute, $value, $locale)->save();
    }

    /**
     * Get the translation value of the given attribute to the given locale.
     */
    public function get(string $attribute, string $locale = null)
    {
        $this->assertAttributeIsTranslatable($attribute);

        try {
            return $this->getOrFail($attribute, $locale ?: $this->getLocale());
        } catch (TranslationMissingException $e) {
            event(new TranslationMissing($this->model, $attribute, $locale));

            return $this->fallback($attribute);
        }
    }

    /**
     * Get the translation value of the given attribute or the fallback value if it is missing.
     */
    public function getOrFail(string $attribute, string $locale = null)
    {
        $this->assertAttributeIsTranslatable($attribute);

        $translation = $this->strategy->get($attribute, $locale ?: $this->getLocale());

        if (is_null($translation)) {
            return null;
        }

        return $this->model->withAttributeGetter($attribute, $translation);
    }

    /**
     * Get the fallback translation of the model.
     */
    public function fallback(string $attribute)
    {
        return $this->model->getOriginalAttribute($attribute);
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
     * Save translations into the database.
     */
    public function save(): void
    {
        $this->strategy->save();
    }

    public function unset()
    {

    }

    /**
     * Delete translation from the model for the given attribute and locale.
     */
    public function delete(string $attribute, string $locale = null)
    {
        // TODO: pass to strategy
    }

    /**
     * Assert that the given attribute is translatable.
     */
    protected function assertAttributeIsTranslatable(string $attribute): void
    {
        if (! $this->model->isTranslatable($attribute)) {
            throw AttributeNotTranslatableException::fromAttribute($attribute);
        }
    }

    /**
     * Add many translations to the model for the given locale.
     */
    public function addMany(array $translations, string $locale = null): void
    {
        $this->setMany($translations, $locale)->save();
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
}
