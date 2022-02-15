<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class SetTranslationTest extends TestCase
{
    /** @test */
    public function it_updates_default_attribute_for_default_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->translation()->add('title', 'Book title in English', 'en');
        $book->save();

        self::assertEquals('Book title in English', $book->title);
        self::assertEmpty(Translation::all());
    }

    /** @test */
    public function it_throws_an_exception_for_not_translatable_attribute(): void
    {
        $book = BookFactory::new()->create();

        $this->expectException(AttributeNotTranslatableException::class);

        $book->translation()->add('id', 'English ID');
    }
}
