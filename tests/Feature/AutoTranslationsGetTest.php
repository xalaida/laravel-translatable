<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\Translatable;

class AutoTranslationsGetTest extends TestCase
{
    /** @test */
    public function it_automatically_retrieves_translations_for_attributes_using_the_current_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->translation()->set('title', 'Моя лучшая книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Моя лучшая книга', $book->title);
    }

    /** @test */
    public function it_uses_original_value_for_attribute_if_translation_is_missing(): void
    {
        $book = BookFactory::new()->create(['title' => 'My legendary book']);

        $this->app->setLocale('ru');

        self::assertEquals('My legendary book', $book->title);
    }

    /** @test */
    public function it_can_return_an_attribute_without_translation(): void
    {
        $book = BookFactory::new()->create(['title' => 'My excellent book']);

        $book->translation()->set('title', 'Моя превосходная книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('My excellent book', $book->getDefaultTranslation('title'));
    }

    /** @test */
    public function it_returns_default_attribute_for_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);

        $book->translation()->set('title', 'Моя лучшая книга', 'ru');

        self::assertEquals('My best book', $book->title);
    }

    /** @test */
    public function it_retrieves_correctly_values_for_not_translatable_attributes(): void
    {
        $book = BookFactory::new()->create(['version' => 5]);

        self::assertEquals(5, $book->version);
    }

    /** @test */
    public function it_does_not_store_resolved_values_back(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);

        $book->translation()->set('title', 'Моя лучшая книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Моя лучшая книга', $book->title);

        DB::connection()->enableQueryLog();

        $book->save();

        self::assertEmpty(DB::connection()->getQueryLog());
    }

    /** @test */
    public function it_does_not_auto_load_translations_when_feature_is_disabled(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);

        $book->translation()->set('title', 'Моя лучшая книга', 'ru');

        $this->app[Translatable::class]->disableAutoLoading();

        $this->app->setLocale('ru');

        self::assertEquals('My best book', $book->title);
    }

    /** @test */
    public function it_does_not_perform_additional_queries_for_loading_translations_when_feature_is_disabled(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);

        $book->translation()->set('title', 'Моя лучшая книга', 'ru');

        $this->app[Translatable::class]->disableAutoLoading();

        $this->app->setLocale('ru');

        DB::connection()->enableQueryLog();

        self::assertEquals('My best book', Book::first()->title);
        self::assertCount(1, DB::connection()->getQueryLog());
    }
}
