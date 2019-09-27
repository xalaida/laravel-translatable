<?php

namespace Nevadskiy\Translatable\Engine;

interface TranslatorEngine
{
    /**
     * Translate the given string.
     *
     * @param string $string
     * @param string $target
     * @param string|null $source
     * @throws TranslationException
     * @return string
     */
    public function translate(string $string, string $target = 'en', string $source = null): string;
}
