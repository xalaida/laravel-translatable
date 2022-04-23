<?php

namespace Nevadskiy\Translatable\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\InteractsWithTranslations;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

/**
 * @mixin Model
 */
trait HasTranslations
{
    use InteractsWithTranslations;

    /**
     * Get the translation strategy.
     */
    protected function getTranslationStrategy(): TranslatorStrategy
    {
        return new ArrayStrategy();
    }
}
