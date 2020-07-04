<?php

namespace Nevadskiy\Translatable\Exceptions;

use DomainException;

class NotTranslatableAttributeException extends DomainException
{
    /**
     * Exception constructor.
     */
    protected function __construct(string $attribute)
    {
        parent::__construct("Attribute {$attribute} is not translatable.");
    }

    /**
     * Create the exception instance from the attribute.
     *
     * @return static
     */
    public static function fromAttribute(string $attribute): self
    {
        return new static($attribute);
    }
}
