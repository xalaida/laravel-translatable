<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AutoTranslationsGetTest extends TestCase
{
    /** @test */
    public function it_automatically_retrieves_translations_for_attributes_using_the_current_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Моя лучшая книга', 'ru');

        $this->app->setLocale('ru');

        $this->assertEquals('Моя лучшая книга', $book->title);
    }

    /** @test */
    public function it_uses_original_value_for_attribute_if_translation_is_missing(): void
    {
        $book = BookFactory::new()->create(['title' => 'My legendary book']);

        $this->app->setLocale('ru');

        $this->assertEquals('My legendary book', $book->title);
    }

    /** @test */
    public function it_can_return_an_attribute_without_translation(): void
    {
        $book = BookFactory::new()->create(['title' => 'My excellent book']);

        $book->translate('title', 'Моя превосходная книга', 'ru');

        $this->app->setLocale('ru');

        $this->assertEquals('My excellent book', $book->getDefaultAttribute('title'));
    }

    /** @test */
    public function it_returns_default_attribute_for_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);

        $book->translate('title', 'Моя лучшая книга', 'ru');

        $this->assertEquals('My best book', $book->title);
    }

    /** @test */
    public function it_retrieves_correctly_values_for_not_translatable_attributes(): void
    {
        $book = BookFactory::new()->create(['version' => 5]);

        $this->assertEquals(5, $book->version);
    }
}
