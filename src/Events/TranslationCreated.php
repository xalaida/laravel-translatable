<?php

namespace Nevadskiy\Translatable\Events;

use Nevadskiy\Translatable\Models\Translation;

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
