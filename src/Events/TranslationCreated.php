<?php

namespace Nevadskiy\Translatable\Events;

use Nevadskiy\Translatable\Behaviours\Single\Models\Translation;

// TODO: refactor to make it convenient for both strategies
class TranslationCreated
{
    /**
     * @var Translation
     */
    public $translation;

    /**
     * Create a new event instance.
     */
    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
    }
}
