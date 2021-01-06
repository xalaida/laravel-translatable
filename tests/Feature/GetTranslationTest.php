<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Exceptions\NotTranslatableAttributeException;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class GetTranslationTest extends TestCase
{
    /** @test */
    public function it_retrieves_default_attribute_for_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);
        $book->translate('title', 'Моя лучшая книга', 'ru');

        self::assertEquals('My best book', $book->getTranslation('title', 'en'));
    }

    /** @test */
    public function it_throws_en_exception_for_not_translatable_attribute(): void
    {
        $book = BookFactory::new()->create();

        $this->expectException(NotTranslatableAttributeException::class);

        $book->getTranslation('id');
    }
}
