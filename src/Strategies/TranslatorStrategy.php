<?php

namespace Nevadskiy\Translatable\Strategies;

interface TranslatorStrategy
{
    // TODO: probably rename into 'read'
    public function get(string $attribute, string $locale);

    // TODO: probably rename into 'create'
    public function set(string $attribute, $value, string $locale);
}
