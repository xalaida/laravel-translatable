<?php

namespace Nevadskiy\Translatable\Engine;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Throwable;

class GoogleTranslateEngine implements TranslatorEngine
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
    public function translate(string $string, string $target = 'en', string $source = null): string
    {
        try {
            usleep(250 * 1000);
            return GoogleTranslate::trans($string, $target, $source);
        } catch (Throwable $e) {
            throw new TranslationException("Cannot load translation for '{$string}'. Reason: {$e->getMessage()}");
        }
    }
}
