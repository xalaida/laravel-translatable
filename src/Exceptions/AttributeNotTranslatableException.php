<?php

namespace Nevadskiy\Translatable\Exceptions;

use DomainException;

class AttributeNotTranslatableException extends DomainException
{
    /**
     * Make a new exception instance.
     */
    protected function __construct(string $attribute)
    {
        parent::__construct("Attribute {$attribute} is not translatable.");
    }

    /**
     * Create the exception instance from the attribute.
     */
    public static function fromAttribute(string $attribute): self
    {
        return new static($attribute);
    }
}
