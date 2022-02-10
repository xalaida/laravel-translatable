<?php

namespace Nevadskiy\Translatable;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\AdditionalTableStrategy;

/**
 * @mixin Model
 */
trait HasTranslationsInAdditionalTable
{
    // TODO: provide a hook to configure the strategy
    public function translation(): Translator
    {
        return new Translator($this->getTranslationStrategy());
    }

    /**
     * Get the translation strategy.
     */
    private function getTranslationStrategy(): AdditionalTableStrategy
    {
        return new AdditionalTableStrategy($this, $this->getConnection());
    }
}
