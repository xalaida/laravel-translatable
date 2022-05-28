<?php

namespace Nevadskiy\Translatable;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslator;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use Nevadskiy\Translatable\Events\TranslationMissing;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;

class Translator
{
    /**
     * The translator locale resolver.
     *
     * @var callable
     */
    protected static $localeResolver;

    /**
     * The fallback translator locale resolver.
     *
     * @var callable
     */
    protected static $fallbackLocaleResolver;

    /**
     * The event dispatcher resolver.
     *
     * @var callable
     */
    protected static $dispatcherResolver;

    /**
     * The translatable model instance.
     *
     * @var Model|InteractsWithTranslator
     */
    protected $model;

    /**
     * The translator strategy instance.
     *
     * @var TranslatorStrategy
     */
    protected $strategy;

    /**
     * The translator locale.
     */
    protected $locale;

    /**
     * Indicates whether the translator should use a fallback behaviour.
     *
     * @var bool
     */
    protected $fallback = true;

    /**
     * The translator fallback locale.
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
    }

    /**
     * Set the locale to the translator.
     */
    public function locale(string $locale): Translator
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the translator locale.
     */
    public function getLocale(): string
    {
        return $this->locale ?: call_user_func(static::$localeResolver);
    }

    /**
     * Set up the fallback locale to the translator.
     */
    public function fallbackLocale(string $locale): Translator
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Get the fallback locale.
     */
    public function getFallbackLocale(): string
    {
        return $this->fallbackLocale ?: call_user_func(static::$fallbackLocaleResolver);
    }

    /**
     * Determine if the given or current locale is a fallback locale.
     */
    public function isFallbackLocale(string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return $locale === $this->getFallbackLocale();
    }

    /**
     * Disable the fallback translator behaviour.
     */
    public function disableFallback(): Translator
    {
        $this->fallback = false;

        return $this;
    }

    /**
     * Enable the fallback translator behaviour.
     */
    public function enableFallback(): Translator
    {
        $this->fallback = true;

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
            $this->fireTranslationMissingEvent($e);

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
     * Set and save the given translation for the model.
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
     * Fire the translation missing event.
     */
    protected function fireTranslationMissingEvent(TranslationMissingException $e): void
    {
        $this->getEventDispatcher()->dispatch(new TranslationMissing($e->model, $e->attribute, $e->locale));
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

    /**
     * Set the translator locale resolver.
     */
    public static function resolveLocaleUsing(callable $locale): void
    {
        static::$localeResolver = $locale;
    }

    /**
     * Set the fallback translator locale resolver.
     */
    public static function resolveFallbackLocaleUsing(callable $locale): void
    {
        static::$fallbackLocaleResolver = $locale;
    }

    /**
     * Set the event dispatcher resolver.
     */
    public static function resolveEventDispatcherUsing(callable $dispatcher): void
    {
        static::$dispatcherResolver = $dispatcher;
    }

    /**
     * Get the event dispatcher instance.
     */
    protected function getEventDispatcher(): Dispatcher
    {
        return call_user_func(static::$dispatcherResolver);
    }
}
