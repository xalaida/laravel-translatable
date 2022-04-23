<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class GetterTranslationTest extends TestCase
{
    /** @test */
    public function it_automatically_retrieves_translations_for_attributes_using_current_locale(): void
    {
        $article = BookFactory::new()->create();

        $article->translator()->add('title', 'Моя лучшая книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Моя лучшая книга', $article->title);
    }

    /** @test */
    public function it_uses_original_value_for_attribute_if_translation_is_missing(): void
    {
        $article = BookFactory::new()->create(['title' => 'My legendary article']);

        $this->app->setLocale('ru');

        self::assertEquals('My legendary article', $article->title);
    }

    /** @test */
    public function it_can_return_an_attribute_without_translation(): void
    {
        $article = BookFactory::new()->create(['title' => 'My excellent article']);

        $article->translator()->add('title', 'Моя превосходная книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('My excellent article', $article->getOriginalAttribute('title'));
    }

    /** @test */
    public function it_returns_original_attribute_for_default_locale(): void
    {
        $article = BookFactory::new()->create(['title' => 'My best article']);

        $article->translator()->add('title', 'Моя лучшая книга', 'ru');

        self::assertEquals('My best article', $article->title);
    }

    /** @test */
    public function it_correctly_retrieves_values_for_non_translatable_attributes(): void
    {
        $article = BookFactory::new()->create(['version' => 5]);

        self::assertEquals(5, $article->version);
    }

    /** @test */
    public function it_does_not_store_retrieved_values_again(): void
    {
        $article = BookFactory::new()->create(['title' => 'My best article']);

        $article->translator()->add('title', 'Моя лучшая книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Моя лучшая книга', $article->title);

        DB::connection()->enableQueryLog();

        $article->save();

        self::assertEmpty(DB::connection()->getQueryLog());
    }
}
