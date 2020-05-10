<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class EagerLoadingTranslationsTest extends TestCase
{
    /** @test */
    public function it_eager_loads_translations_for_current_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Книга про черепах', 'ru');
        $book->translate('title', 'En bok om sköldpaddor', 'sv');
        $book->translate('title', 'Ein Buch über Schildkröten', 'de');

        $this->app->setLocale('ru');


        [$book] = Book::all();

        $this->assertTrue($book->relationLoaded('translations'));
        $this->assertCount(1, $book->translations);
        $this->assertEquals('ru', $book->translations[0]->locale);
    }

    /** @test */
    public function it_performs_only_two_queries_for_translations_eager_loading(): void
    {
        BookFactory::new()->create()->translate('title', 'Первая книга', 'ru');
        BookFactory::new()->create()->translate('title', 'Вторая книга', 'ru');
        BookFactory::new()->create()->translate('title', 'Третья книга', 'ru');

        $this->app->setLocale('ru');

        DB::enableQueryLog();

        [$book1, $book2, $book3] = Book::all();

        $this->assertEquals('Первая книга', $book1->title);
        $this->assertEquals('Вторая книга', $book2->title);
        $this->assertEquals('Третья книга', $book3->title);

        $this->assertCount(2, DB::getQueryLog());
    }

    /** @test */
    public function it_allows_to_remove_translations_scope_from_query_builder(): void
    {
        BookFactory::new()->create()->translate('title', 'Книга о черепахах', 'ru');

        [$book] = Book::withoutTranslations()->get();

        $this->assertFalse($book->relationLoaded('translations'));
    }
}
