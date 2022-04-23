<?php

namespace Nevadskiy\Translatable\Exceptions;

use DomainException;
use Illuminate\Database\Eloquent\Model;

class TranslationMissingException extends DomainException
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
     * Create the exception instance for the given model.
     */
    public function __construct(Model $model, string $attribute, string $locale)
    {
        parent::__construct("The requested translation is missing.");
        $this->model = $model;
        $this->attribute = $attribute;
        $this->locale = $locale;
    }
}
