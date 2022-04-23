<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslations;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use Nevadskiy\Translatable\Events\TranslationNotFound;
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

    // TODO: add possibility to log out warnings with missing translations.

    /**
     * Get the translation value of the given attribute or the fallback value if it is missing.
     *
     * @return mixed
     */
    public function getOrFallback(string $attribute, string $locale = null)
    {
        $value = $this->get($attribute, $locale);

        // TODO: make the strategy to be responsible how to retrieve fallback translation.
        if (is_null($value)) {
            return $this->model->getOriginalAttribute($attribute);
        }

        return $value;
    }

    /**
     * Get the translation value of the given attribute to the given locale.
     *
     * @return mixed
     */
    public function get(string $attribute, string $locale = null)
    {
        $this->assertAttributeIsTranslatable($attribute);

        $raw = $this->raw($attribute, $locale);

        if (is_null($raw)) {
            return null;
        }

        return $this->model->withAttributeGetter($attribute, $raw);
    }

    /**
     * Get list of translations for all translatable attributes for the given locale.
     */
    public function toArray(string $locale = null): array
    {
        $locale = $locale ?: $this->getLocale();

        $translations = [];

        foreach ($this->model->getTranslatable() as $attribute) {
            $translations[$attribute] = $this->getOrFallback($attribute, $locale);
        }

        return $translations;
    }

    /**
     * Get raw translation value for the attribute.
     */
    public function raw(string $attribute, string $locale = null)
    {
        $locale = $locale ?: $this->getLocale();

        $translation = $this->strategy->get($attribute, $locale);

        if (is_null($translation)) {
            // TODO: use DI model dispatcher.
            event(new TranslationNotFound($this->model, $attribute, $locale));
        }

        return $translation;
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

    public function delete()
    {

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
