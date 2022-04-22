<?php

namespace Nevadskiy\Translatable\Events;

use Illuminate\Database\Eloquent\Model;

class TranslationNotFound
{
    /**
     * @var Model
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
     */
    public function __construct(Model $model, string $attribute, string $locale)
    {
        $this->model = $model;
        $this->attribute = $attribute;
        $this->locale = $locale;
    }
}
