<?php

namespace Nevadskiy\Translatable\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;

class ArrayStrategy implements TranslatorStrategy
{
    /**
     * The translations.
     *
     * @var array
     */
    private $translations;

    /**
     * The translatable model instance.
     *
     * @var Model
     */
    private $model;

    /**
     * Make a new strategy instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function get(string $attribute, string $locale)
    {
        if (! isset($this->translations[$locale][$attribute])) {
            throw new TranslationMissingException($this->model, $attribute, $locale);
        }

        return $this->translations[$locale][$attribute];
    }

    /**
     * @inheritDoc
     */
    public function set(string $attribute, $value, string $locale): void
    {
        $this->translations[$locale][$attribute] = $value;
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function delete(): void
    {
        //
    }
}
