<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class TranslatableScopesTest extends TestCase
{
    /** @test */
    public function it_can_retrieve_translatable_model_by_attribute_value_and_locale(): void
    {
        $book1 = BookFactory::new()->create();
        $book1->translate('title', 'Книга о попугаях', 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translate('title', 'Книга о жирафах', 'ru');

        $book3 = BookFactory::new()->create();
        $book3->translate('title', 'Книга о пингвинах', 'ru');

        $result = Book::whereTranslatable('title', 'Книга о жирафах', 'ru')->first();

        $this->assertTrue($result->is($book2));
    }
}
