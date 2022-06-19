<?php

namespace Nevadskiy\Translatable\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslator;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * @mixin Model
 */
trait HasTranslations
{
    use InteractsWithTranslator;

    /**
     * Get the translation strategy.
     */
    protected function getTranslationStrategy(): TranslatorStrategy
    {
        return new ArrayStrategy($this);
    }
}
