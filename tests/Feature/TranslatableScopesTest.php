<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class TranslatableScopesTest extends TestCase
{
    /** @test */
    public function it_retrieves_model_by_default_value(): void
    {
        $this->app->setLocale('ru');

        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);
        $book->translate('title', 'Книга про дельфинов', 'ru');

        $result = Book::whereTranslatable('title', 'Book about dolphins')->first();

        $this->assertTrue($result->is($book));
    }

    /** @test */
    public function it_can_retrieve_translatable_model_by_attribute_value_within_all_locales(): void
    {
        $this->app->setLocale('ru');

        $book1 = BookFactory::new()->create();
        $book1->translate('title', 'Книга про собак', 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translate('title', 'Книга про собак', 'uk');

        $book3 = BookFactory::new()->create();
        $book3->translate('title', 'Libro sobre perros', 'es');

        $books = Book::whereTranslatable('title', 'Книга про собак')->get();

        $this->assertCount(2, $books);
        $this->assertTrue($books[0]->is($book1));
        $this->assertTrue($books[1]->is($book2));
    }

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

    /** @test */
    public function it_does_not_retrieve_model_by_incorrect_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);
        $book->translate('title', 'Книга про дельфинов', 'ru');

        $result = Book::whereTranslatable('title', 'Book about dolphins', 'ru')->first();

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_retrieve_model_by_passing_default_locale(): void
    {
        $defaultLocale = $this->app['config']['app']['fallback_locale'];

        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);

        $result = Book::whereTranslatable('title', 'Book about dolphins', $defaultLocale)->first();

        $this->assertTrue($result->is($book));
    }

    /** @test */
    public function it_does_not_retrieve_wrong_translated_model_by_passing_default_locale(): void
    {
        $defaultLocale = $this->app['config']['app']['fallback_locale'];

        $wrongTranslatedBook = BookFactory::new()->create(['title' => 'Book about birds']);
        $wrongTranslatedBook->translate('title', 'Book about dolphins', $defaultLocale);

        $result = Book::whereTranslatable('title', 'Book about dolphins', $defaultLocale)->first();

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_retrieve_models_using_like_operator(): void
    {
        $book1 = BookFactory::new()->create();
        $book1->translate('title', 'Книга о птицах', 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translate('title', 'Книга о дельфинах', 'ru');

        $book3 = BookFactory::new()->create();
        $book3->translate('title', 'Книга про собак', 'ru');

        $result = Book::whereTranslatable('title', 'Книга о%', null, 'LIKE')->get();

        $this->assertCount(2, $result);
        $this->assertTrue($result[0]->is($book1));
        $this->assertTrue($result[1]->is($book2));
    }
}
