<?php

namespace Nevadskiy\Translatable;

use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

class Translator
{
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
    public function __construct(TranslatorStrategy $strategy)
    {
        $this->strategy = $strategy;
        $this->locale = app()->getLocale();
        $this->defaultLocale = 'en';
    }

    /**
     * Get the translator locale.
     */
    public function getLocale(): string
    {
        return $this->locale ?: $this->defaultLocale;
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

    public function set(string $attribute, $value, string $locale = null)
    {
        return $this->strategy->set($attribute, $value, $locale);
    }

    public function setMany(array $translations, string $locale = null): void
    {
        foreach ($translations as $attribute => $value) {
            $this->set($attribute, $value, $locale);
        }
    }
}
