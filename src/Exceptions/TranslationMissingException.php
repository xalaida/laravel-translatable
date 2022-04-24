<?php

namespace Nevadskiy\Translatable\Exceptions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class TranslationMissingException extends RuntimeException
{
    /**
     * The translatable model instance.
     *
     * @var Model
     */
    public $model;

    /**
     * An attribute of the missing translation.
     *
     * @var string
     */
    public $attribute;

    /**
     * A locale of the missing translation.
     *
     * @var string
     */
    public $locale;

    /**
     * Make a new exception instance.
     */
    public function __construct(Model $model, string $attribute, string $locale)
    {
        parent::__construct("The requested translation is missing.");
        $this->model = $model;
        $this->attribute = $attribute;
        $this->locale = $locale;
    }
}
