<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class EagerLoadingTranslationsTest extends TestCase
{
    /** @test */
    public function it_automatically_eager_loads_translations_for_current_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->translator()->add('title', 'Книга про черепах', 'ru');
        $book->translator()->add('title', 'En bok om sköldpaddor', 'sv');
        $book->translator()->add('title', 'Ein Buch über Schildkröten', 'de');

        $this->app->setLocale('ru');

        [$book] = Book::all();

        DB::enableQueryLog();

        self::assertTrue($book->relationLoaded('translations'));
        self::assertCount(1, $book->translations);
        self::assertEquals('ru', $book->translations[0]->locale);
        self::assertEquals('Книга про черепах', $book->translations[0]->value);
        self::assertEmpty(DB::getQueryLog());
    }

    /** @test */
    public function it_performs_only_two_queries_for_translations_eager_loading(): void
    {
        [$book1, $book2, $book3] = BookFactory::new()->createMany(3);

        $book1->translation()->add('title', 'Первая книга', 'ru');
        $book2->translation()->add('title', 'Вторая книга', 'ru');
        $book3->translation()->add('title', 'Третья книга', 'ru');

        $this->app->setLocale('ru');

        DB::enableQueryLog();

        [$book1, $book2, $book3] = Book::all();

        self::assertEquals('Первая книга', $book1->title);
        self::assertEquals('Вторая книга', $book2->title);
        self::assertEquals('Третья книга', $book3->title);

        self::assertCount(2, DB::getQueryLog());
    }

    /** @test */
    public function it_allows_to_disable_eager_loading_on_query_builder(): void
    {
        $book = BookFactory::new()->create();

        $book->translator()->add('title', 'Книга про черепах', 'ru');

        [$book] = Book::query()->withoutTranslations()->get();

        self::assertFalse($book->relationLoaded('translations'));
    }
}
