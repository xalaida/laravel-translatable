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

    /**
     * Set the translation for the model.
     */
    public function set(string $attribute, $value, string $locale = null): Translator
    {
        $this->assertTranslatableAttribute($attribute);

        if ($this->isDefaultLocale($locale)) {
            $this->model->setAttribute($attribute, $value);
        } else {
            $this->attributesToSet[$locale][$attribute] = $this->model->withAttributeMutators($attribute, $value);
        }

        return $this;
    }

    public function add(string $attribute, $value, string $locale): void
    {
        $this->set($attribute, $value, $locale)->save();
    }

    public function get(string $attribute, string $locale)
    {
        // TODO: add possibility to log out warnings with missing translations.

        return $this->strategy->get($attribute, $locale);
    }

    /**
     * Save translations into the database.
     */
    public function save(): void
    {
        foreach ($this->attributesToSet as $locale => $attributes) {
            foreach ($attributes as $attribute => $value) {
                $this->strategy->set($attribute, $value, $locale);
            }
        }

        $this->attributesToSet = [];
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
