<?php

namespace Nevadskiy\Translatable\Events;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

class TranslationNotFoundEvent
{
    /**
     * @var Model|HasTranslations
     */
    public $model;

    /**
     * @var string
     */
    public $attribute;

    /**
     * @var string
     */
    public $locale;

    /**
     * Create a new event instance.
     *
     * @param Model|HasTranslations $model
     * @param string $attribute
     * @param string $locale
     */
    public function __construct(Model $model, string $attribute, string $locale)
    {
        $this->model = $model;
        $this->attribute = $attribute;
        $this->locale = $locale;
    }
}
