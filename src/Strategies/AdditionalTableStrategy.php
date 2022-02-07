<?php

namespace Nevadskiy\Translatable\Strategies;

class AdditionalTableStrategy implements TranslatorStrategy
{
    public function get(string $attribute, string $locale)
    {
        return 'Свитер с оленями';
    }

    public function set(string $attribute, $value, string $locale)
    {
        //
    }
}
