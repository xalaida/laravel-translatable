<?php

namespace Nevadskiy\Translatable\Exceptions;

use DomainException;

class AttributeNotTranslatableException extends DomainException
{
    /**
     * Create the exception instance from the attribute.
     */
    public static function fromAttribute(string $attribute): self
    {
        return new static("Attribute {$attribute} is not translatable.");
    }
}
