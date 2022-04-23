<?php

namespace Nevadskiy\Translatable\Tests\Support;

use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

class ArrayStrategy implements TranslatorStrategy
{
    /**
     * The translations.
     *
     * @var array
     */
    private $translations;

    /**
     * @inheritDoc
     */
    public function get(string $attribute, string $locale)
    {
        return $this->translations[$locale][$attribute] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        $this->translations[$locale][$attribute] = $value;
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        // log saved...
    }
}
