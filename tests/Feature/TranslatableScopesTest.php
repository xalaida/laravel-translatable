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
        $book->translation()->set('title', 'Книга про дельфинов', 'ru');

        $result = Book::whereTranslatable('title', 'Book about dolphins')->first();

        self::assertTrue($result->is($book));
    }

    /** @test */
    public function it_can_retrieve_translatable_model_by_attribute_value_within_all_locales(): void
    {
        $this->app->setLocale('ru');

        $book1 = BookFactory::new()->create();
        $book1->translation()->set('title', 'Книга про собак', 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translation()->set('title', 'Книга про собак', 'uk');

        $book3 = BookFactory::new()->create();
        $book3->translation()->set('title', 'Libro sobre perros', 'es');

        $books = Book::whereTranslatable('title', 'Книга про собак')->get();

        self::assertCount(2, $books);
        self::assertTrue($books[0]->is($book1));
        self::assertTrue($books[1]->is($book2));
    }

    /** @test */
    public function it_can_retrieve_translatable_model_by_attribute_value_and_locale(): void
    {
        $book1 = BookFactory::new()->create();
        $book1->translation()->set('title', 'Книга о попугаях', 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translation()->set('title', 'Книга о жирафах', 'ru');

        $book3 = BookFactory::new()->create();
        $book3->translation()->set('title', 'Книга о пингвинах', 'ru');

        $result = Book::whereTranslatable('title', 'Книга о жирафах', 'ru')->first();

        self::assertTrue($result->is($book2));
    }

    /** @test */
    public function it_does_not_retrieve_model_by_incorrect_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);
        $book->translation()->set('title', 'Книга про дельфинов', 'ru');

        $result = Book::whereTranslatable('title', 'Book about dolphins', 'ru')->first();

        self::assertNull($result);
    }

    /** @test */
    public function it_can_retrieve_model_by_passing_default_locale(): void
    {
        $defaultLocale = $this->app['config']['app']['fallback_locale'];

        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);

        $result = Book::whereTranslatable('title', 'Book about dolphins', $defaultLocale)->first();

        self::assertTrue($result->is($book));
    }

    /** @test */
    public function it_can_retrieve_models_using_like_operator(): void
    {
        $book1 = BookFactory::new()->create();
        $book1->translation()->set('title', 'Книга о птицах', 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translation()->set('title', 'Книга о дельфинах', 'ru');

        $book3 = BookFactory::new()->create();
        $book3->translation()->set('title', 'Книга про собак', 'ru');

        $result = Book::whereTranslatable('title', 'Книга о%', null, 'LIKE')->get();

        self::assertCount(2, $result);
        self::assertTrue($result[0]->is($book1));
        self::assertTrue($result[1]->is($book2));
    }

    /** @test */
    public function it_can_retrieve_translatable_model_by_archived_translation(): void
    {
        $this->app->setLocale('ru');

        $book = BookFactory::new()->create();
        $book->translation()->set('title', 'Книга про собак', 'ru');
        $book->archiveTranslation('title', 'Старинная книга про собак', null);

        $anotherBook = BookFactory::new()->create();
        $anotherBook->translation()->set('title', 'Книга о дельфинах', 'ru');

        $books = Book::whereTranslatable('title', 'Старинная книга про собак')->get();

        self::assertCount(1, $books);
        self::assertTrue($books[0]->is($book));
    }

    /** @test */
    public function it_can_retrieve_translatable_model_by_archived_translation_in_default_locale(): void
    {
        $book = BookFactory::new()->create();
        $book->archiveTranslation('title', 'Old book');

        $books = Book::whereTranslatable('title', 'Old book', 'en')->get();

        self::assertCount(1, $books);
        self::assertTrue($books[0]->is($book));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_in_current_locale(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translation()->set('title', 'Первая книга', 'ru');
        $book2->translation()->set('title', 'Вторая книга', 'ru');

        $this->app->setLocale('ru');

        $books = Book::query()->orderByTranslatable('title')->get();

        self::assertTrue($books[0]->is($book2));
        self::assertTrue($books[1]->is($book1));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_in_descending_order(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translation()->set('title', 'Первая книга', 'ru');
        $book2->translation()->set('title', 'Вторая книга', 'ru');

        $this->app->setLocale('ru');

        $books = Book::query()->orderByTranslatable('title', 'desc')->get();

        self::assertTrue($books[0]->is($book1));
        self::assertTrue($books[1]->is($book2));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_for_custom_locale(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translation()->set('title', 'Первая книга', 'ru');
        $book2->translation()->set('title', 'Вторая книга', 'ru');

        $books = Book::query()->orderByTranslatable('title', 'asc', 'ru')->get();

        self::assertTrue($books[0]->is($book2));
        self::assertTrue($books[1]->is($book1));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_in_default_locale(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $books = Book::query()->orderByTranslatable('title')->get();

        self::assertTrue($books[0]->is($book1));
        self::assertTrue($books[1]->is($book2));
    }
}
