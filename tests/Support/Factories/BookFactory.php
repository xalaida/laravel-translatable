<?php

namespace Nevadskiy\Translatable\Tests\Support\Factories;

use Nevadskiy\Translatable\Tests\Support\Models\Book;

class BookFactory
{
    /**
     * Static constructor.
     *
     * @return static
     */
    public static function new(): self
    {
        return new static;
    }

    /**
     * Create a new book instance and save it into the database.
     *
     * @param array $attributes
     * @return Book
     */
    public function create(array $attributes = []): Book
    {
        $book = new Book(array_merge($this->getDefaults(), $attributes));
        $book->save();

        return $book;
    }

    /**
     * Get default values.
     *
     * @return array
     */
    private function getDefaults(): array
    {
        return [
            'title' => 'Book testing title',
            'description' => 'Book testing description',
            'version' => '1',
        ];
    }
}
