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
     * Make a new translator instance.
     */
    public function __construct(TranslatorStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function get(string $attribute, string $locale)
    {
        // TODO: add possibility to log out warnings with missing translations.

        return $this->strategy->get($attribute, $locale);
    }

    public function set(string $attribute, $value, string $locale)
    {
        return $this->strategy->set($attribute, $value, $locale);
    }
}
