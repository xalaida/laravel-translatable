<?php

namespace Nevadskiy\Translatable\Tests\Support\Factories;

use Nevadskiy\Translatable\Tests\Support\Models\Product;

class ProductFactory
{
    /**
     * Make a new factory instance.
     */
    public static function new(): self
    {
        return new static();
    }

    /**
     * Create a new book instance and save it into the database.
     */
    public function create(array $attributes = []): Product
    {
        $product = new Product();
        $product->forceFill(array_merge($this->getDefaults(), $attributes));
        $product->save();

        return $product;
    }

    /**
     * Get default values.
     */
    private function getDefaults(): array
    {
        return [
            'title' => 'Book testing title',
            'description' => 'Book testing description',
        ];
    }
}
