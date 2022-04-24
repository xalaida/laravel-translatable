<?php

namespace Nevadskiy\Translatable\Exceptions;

use RuntimeException;

class AttributeNotTranslatableException extends RuntimeException
{
    /**
     * The name of the attribute.
     *
     * @var string
     */
    public $attribute;

    /**
     * Make a new exception instance.
     */
    public function __construct(string $attribute)
    {
        parent::__construct("Attribute {$attribute} is not translatable.");
        $this->attribute = $attribute;
    }
}
