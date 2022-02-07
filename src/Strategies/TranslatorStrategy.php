<?php

namespace Nevadskiy\Translatable\Strategies;

interface TranslatorStrategy
{
    public function get(string $attribute, string $locale);

    public function set(string $attribute, $value, string $locale);
}
