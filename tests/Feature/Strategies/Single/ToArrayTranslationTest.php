<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class ToArrayTranslationTest extends TestCase
{
    /** @test */
    public function it_returns_an_array_with_model_translations(): void
    {
        $book = BookFactory::new()->create([
            'title' => 'My first book',
            'description' => 'Book about dolphins',
        ]);

        $book->translator()->add('title', 'Моя первая книга', 'ru');
        $book->translator()->add('description', 'Книга о дельфинах', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals([
           'title' => 'Моя первая книга',
           'description' => 'Книга о дельфинах',
        ], $book->translator()->toArray());
    }

    /** @test */
    public function it_transforms_to_array_using_translatable_values(): void
    {
        $book = BookFactory::new()->create([
            'title' => 'My first book',
            'description' => 'Book about dolphins',
        ]);

        $book->translator()->add('title', 'Моя первая книга', 'ru');
        $book->translator()->add('description', 'Книга о дельфинах', 'ru');

        $this->app->setLocale('ru');

        $bookArray = $book->toArray();

        self::assertEquals('Моя первая книга', $bookArray['title']);
        self::assertEquals('Книга о дельфинах', $bookArray['description']);
    }

    /** @test */
    public function it_transforms_to_array_using_model_accessors(): void
    {
        $book = BookFactory::new()->create([
            'title' => 'My first book',
            'description' => 'Book about dolphins',
        ]);

        $book->translator()->add('title', 'первая книга', 'ru');

        $this->app->setLocale('ru');

        $bookArray = $book->toArray();

        self::assertEquals('Первая книга', $bookArray['title']);
        self::assertEquals('Book about dolphins', $bookArray['description']);
    }
}
