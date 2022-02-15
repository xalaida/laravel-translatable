<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Events\TranslationNotFound;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

class Translator
{
    /**
     * A list of pending translation insertions.
     *
     * @var array
     */
    protected $pendingTranslations = [];

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
            $this->model->setOriginalAttribute($attribute, $value);
        } else {
            $this->pendingTranslations[$locale][$attribute] = $this->model->withAttributeSetter($attribute, $value);
        }

        return $this;
    }

    public function add(string $attribute, $value, string $locale): void
    {
        $this->set($attribute, $value, $locale)->save();
    }

    // TODO: add possibility to log out warnings with missing translations.

    /**
     * Get the translation value of the given attribute or the original value if it is missing.
     *
     * TODO: support original values to be stored within translations table.
     */
    public function getOrOriginal(string $attribute, string $locale = null)
    {
        $value = $this->get($attribute, $locale);

        if (is_null($value)) {
            return $this->model->getOriginalAttribute($attribute);
        }

        return $value;
    }

    public function get(string $attribute, string $locale = null)
    {
        $this->assertTranslatableAttribute($attribute);

        if ($this->isDefaultLocale($locale)) {
            return $this->model->getOriginalAttribute($attribute);
        }

        $raw = $this->raw($attribute, $locale);

        if (is_null($raw)) {
            return null;
        }

        return $this->model->withAttributeGetter($attribute, $raw);
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
        foreach ($this->pendingTranslations as $locale => $attributes) {
            foreach ($attributes as $attribute => $value) {
                $this->strategy->set($attribute, $value, $locale);
            }
        }

        $this->pendingTranslations = [];
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

    public function setMany(array $translations, string $locale = null): Translator
    {
        foreach ($translations as $attribute => $value) {
            $this->set($attribute, $value, $locale);
        }

        return $this;
    }

    public function addMany(array $translations, string $locale = null): void
    {
        $this->setMany($translations, $locale)->save();
    }
}
