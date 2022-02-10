<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

class Translator
{
    /**
     * @TODO rename
     * @var array
     */
    protected $attributesToSet = [];

    /**
     * @TODO rename
     * @var array
     */
    protected $attributesToGet = [];

    /**
     * The translatable model instance.
     *
     * @var Model|HasTranslations
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
     * The current locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * Make a new translator instance.
     */
    public function __construct(Model $model, TranslatorStrategy $strategy)
    {
        $this->model = $model;
        $this->strategy = $strategy;
        $this->locale = app()->getLocale();
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
    public function isDefaultLocale(string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return $locale === $this->defaultLocale;
    }

    public function get(string $attribute, string $locale)
    {
        // TODO: add possibility to log out warnings with missing translations.

        return $this->strategy->get($attribute, $locale);
    }

    public function save()
    {

    }

    public function unset()
    {

    }

    public function delete()
    {

    }

    // TODO: rewrite to either save or just set prepared value.
    public function set(string $attribute, $value, string $locale = null)
    {
        $this->assertTranslatableAttribute($attribute);

        if ($this->isDefaultLocale($locale)) {
            // TODO: it is not saving method.
            return $this->model->setAttribute($attribute, $value);
        }

        // TODO: it is saving method. (probably add flush method)
        return $this->strategy->set($attribute, $this->model->withAttributeMutators($attribute, $value), $locale);
    }

    /**
     * Assert that the given attribute is translatable.
     */
    protected function assertTranslatableAttribute(string $attribute): void
    {
        if (! $this->model->isTranslatable($attribute)) {
            throw AttributeNotTranslatableException::fromAttribute($attribute);
        }
    }

    public function setMany(array $translations, string $locale = null): void
    {
        foreach ($translations as $attribute => $value) {
            $this->set($attribute, $value, $locale);
        }
    }
}
