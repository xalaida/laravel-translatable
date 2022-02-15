<?php

namespace Nevadskiy\Translatable\Tests\Feature\SingleTableStrategy;

use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class GetTranslationTest extends TestCase
{
    /** @test */
    public function it_retrieves_default_value_for_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);
        $book->translation()->add('title', 'Моя лучшая книга', 'ru');

        self::assertEquals('My best book', $book->translation()->get('title', 'en'));
    }

    /** @test */
    public function it_throws_an_exception_for_not_translatable_attribute(): void
    {
        $book = BookFactory::new()->create();

        $this->expectException(AttributeNotTranslatableException::class);

        $book->translation()->get('id');
    }
}
