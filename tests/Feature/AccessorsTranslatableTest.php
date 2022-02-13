<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AccessorsTranslatableTest extends TestCase
{
    /** @test */
    public function it_uses_model_accessors_for_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $book->translation()->add('title', 'моя книга', 'ru');

        self::assertEquals('Моя книга', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_still_applies_accessors_to_original_attributes(): void
    {
        $book = BookFactory::new()->create(['title' => 'my book']);

        self::assertEquals('My book', $book->title);
    }

    /** @test */
    public function it_applies_accessors_to_attributes_with_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'my book']);

        $book->translation()->add('title', 'моя книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('My book', $book->getDefaultTranslation('title'));
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_accessor_to_translation(): void
    {
        $book = BookFactory::new()->create(['title' => 'my book']);

        $book->translation()->add('title', 'моя книга', 'ru');

        $book->getTranslation('title', 'ru');

        self::assertEquals('my book', $book->getRawOriginal('title'));
    }

    /** @test */
    public function it_applies_accessors_using_auto_translatable_getter(): void
    {
        $book = BookFactory::new()->create();

        $this->app->setLocale('ru');

        $book->title = 'моя книга';

        self::assertEquals('Моя книга', $book->title);
    }

    /** @test */
    public function it_returns_raw_translation_value_using_the_current_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->translation()->add('title', 'моя книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('моя книга', $book->getRawTranslation('title', 'ru'));
    }

    /** @test */
    public function it_stores_correctly_values_after_applied_accessors(): void
    {
        $book = BookFactory::new()->create();

        $this->app->setLocale('ru');

        $book->title = 'моя книга';
        $book->save();

        self::assertEquals('Моя книга', $book->title);
        $book->save();

        $book = $book->fresh();

        self::assertEquals('моя книга', $book->getRawTranslation('title', 'ru'));
    }

    /** @test */
    public function it_uses_model_accessors_which_is_not_translatable_correctly(): void
    {
        $book = BookFactory::new()->create();

        $book->translation()->add('description', 'Книга о собаках', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Кни...', $book->description_short);
    }
}
