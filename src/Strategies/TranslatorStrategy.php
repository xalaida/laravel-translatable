<?php

namespace Nevadskiy\Translatable\Strategies;

interface TranslatorStrategy
{
    // TODO: add load method

    /**
     * Get the translation value for the given attribute and locale.
     */
    public function get(string $attribute, string $locale);

    /**
     * Set the translation value of the given attribute and locale.
     *
     * @param mixed $value
     */
    public function set(string $attribute, $value, string $locale): void;

    /**
     * Save all pending translations into the database.
     */
    public function save(): void;
}
