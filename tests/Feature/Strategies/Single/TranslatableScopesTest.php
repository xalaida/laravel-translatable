<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

// TODO: split into multiple test cases
class TranslatableScopesTest extends TestCase
{
    /** @test */
    public function it_retrieves_model_by_default_value(): void
    {
        $this->app->setLocale('uk');

        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);
        $book->translator()->add('title', 'Книга про дельфинов', 'uk');

        $result = Book::whereTranslatable('title', 'Book about dolphins')->first();

        self::assertTrue($result->is($book));
    }

    /** @test */
    public function it_can_retrieve_translatable_model_by_attribute_value_within_all_locales(): void
    {
        $this->app->setLocale('uk');

        $book1 = BookFactory::new()->create();
        $book1->translator()->add('title', 'Книга про собак', 'uk');

        $book2 = BookFactory::new()->create();
        $book2->translator()->add('title', 'Книга про собак', 'uk');

        $book3 = BookFactory::new()->create();
        $book3->translator()->add('title', 'Libro sobre perros', 'es');

        $books = Book::whereTranslatable('title', 'Книга про собак')->get();

        self::assertCount(2, $books);
        self::assertTrue($books[0]->is($book1));
        self::assertTrue($books[1]->is($book2));
    }

    /** @test */
    public function it_can_retrieve_translatable_model_by_attribute_value_and_locale(): void
    {
        $book1 = BookFactory::new()->create();
        $book1->translator()->add('title', 'Книга о попугаях', 'uk');

        $book2 = BookFactory::new()->create();
        $book2->translator()->add('title', 'Книга о жирафах', 'uk');

        $book3 = BookFactory::new()->create();
        $book3->translator()->add('title', 'Книга о пингвинах', 'uk');

        $result = Book::whereTranslatable('title', 'Книга о жирафах', 'uk')->first();

        self::assertTrue($result->is($book2));
    }

    /** @test */
    public function it_does_not_retrieve_model_by_incorrect_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);
        $book->translator()->add('title', 'Книга про дельфинов', 'uk');

        $result = Book::whereTranslatable('title', 'Book about dolphins', 'uk')->first();

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
        $book1->translator()->add('title', 'Книга о птицах', 'uk');

        $book2 = BookFactory::new()->create();
        $book2->translator()->add('title', 'Книга о дельфинах', 'uk');

        $book3 = BookFactory::new()->create();
        $book3->translator()->add('title', 'Книга про собак', 'uk');

        $result = Book::whereTranslatable('title', 'Книга о%', null, 'LIKE')->get();

        self::assertCount(2, $result);
        self::assertTrue($result[0]->is($book1));
        self::assertTrue($result[1]->is($book2));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_in_current_locale(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translator()->add('title', 'Первая книга', 'uk');
        $book2->translator()->add('title', 'Вторая книга', 'uk');

        $this->app->setLocale('uk');

        $books = Book::query()->orderByTranslatable('title')->get();

        self::assertTrue($books[0]->is($book2));
        self::assertTrue($books[1]->is($book1));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_in_descending_order(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translator()->add('title', 'Первая книга', 'uk');
        $book2->translator()->add('title', 'Вторая книга', 'uk');

        $this->app->setLocale('uk');

        $books = Book::query()->orderByTranslatable('title', 'desc')->get();

        self::assertTrue($books[0]->is($book1));
        self::assertTrue($books[1]->is($book2));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_for_custom_locale(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translator()->add('title', 'Первая книга', 'uk');
        $book2->translator()->add('title', 'Вторая книга', 'uk');

        $books = Book::query()->orderByTranslatable('title', 'asc', 'uk')->get();

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
