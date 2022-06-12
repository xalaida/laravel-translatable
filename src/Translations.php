<?php

namespace Nevadskiy\Translatable;

use ArrayIterator;
use IteratorAggregate;

class Translations implements IteratorAggregate
{
    /**
     * The array of translations.
     *
     * @var array<string $locale, mixed $value>
     */
    protected $translations;

    /**
     * Make a new list of translations.
     */
    public function __construct(array $translations)
    {
        $this->translations = $translations;
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->translations);
    }
}
