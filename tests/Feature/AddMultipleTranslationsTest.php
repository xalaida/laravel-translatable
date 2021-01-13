<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Exceptions\NotTranslatableAttributeException;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AddMultipleTranslationsTest extends TestCase
{
    /** @test */
    public function it_adds_multiple_translations_to_the_model_using_same_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $book->addTranslation('title', 'Первая книга', 'ru');
        $book->addTranslation('title', 'Моя первая книга', 'ru');

        self::assertCount(2, $book->translations);
        self::assertEquals('Первая книга', $book->translations[0]->value);
        self::assertEquals('ru', $book->translations[0]->locale);
        self::assertEquals('Моя первая книга', $book->translations[1]->value);
        self::assertEquals('ru', $book->translations[1]->locale);
    }

    /** @test */
    public function it_immediately_returns_preferred_translation_first_if_multiple_translations_have_added(): void
    {
        $book = BookFactory::new()->create(['title' => 'Original book']);

        $book->addTranslation('title', 'Неоригинальная книга', 'ru', false);
        $book->addTranslation('title', 'Оригинальная книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Оригинальная книга', $book->title);
    }

    /** @test */
    public function it_returns_preferred_translation_first_if_multiple_translations_have_added(): void
    {
        $book = BookFactory::new()->create(['title' => 'Original book']);

        $book->addTranslation('title', 'Неоригинальная книга', 'ru', false);
        $book->addTranslation('title', 'Оригинальная книга', 'ru', true);

        $this->app->setLocale('ru');

        self::assertEquals('Оригинальная книга', $book->fresh()->title);
    }

    /** @test */
    public function it_adds_translation_using_current_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'Book about dogs']);

        $this->app->setLocale('ru');

        $translation = $book->addTranslation('title', 'Книга про собак');

        self::assertEquals('ru', $translation->locale);
    }

    /** @test */
    public function it_adds_translation_applying_attribute_mutators(): void
    {
        $book = BookFactory::new()->create();

        $book->addTranslation('title', 'Очень очень длинное название для книги', 'ru');

        self::assertEquals('Очень очень длинное название д...', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_throws_an_exception_for_not_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $this->expectException(NotTranslatableAttributeException::class);

        $book->addTranslation('version', 5, 'ru');
    }

    /** @test */
    public function it_adds_translation_even_for_default_locale_as_not_preferred(): void
    {
        $book = BookFactory::new()->create(['title' => 'Primary title']);

        $book->addTranslation('title', 'Secondary title', 'en', true);

        self::assertCount(1, $book->translations);
        self::assertFalse($book->translations[0]->is_preferred);
    }

    /** @test */
    public function it_does_not_duplicate_translations(): void
    {
        $book = BookFactory::new()->create();

        $book->addTranslation('title', 'Моя книга', 'ru');
        $book->addTranslation('title', 'Моя книга', 'ru');

        self::assertCount(1, $book->translations);
    }

    /** @test */
    public function it_switches_previous_preferred_values_to_not_preferred(): void
    {
        $book = BookFactory::new()->create();

        $translation1 = $book->addTranslation('title', 'Моя книга', 'ru', true);
        $translation2 = $book->addTranslation('title', 'Моя книга (2)', 'ru', true);

        self::assertFalse($translation1->fresh()->is_preferred);
        self::assertTrue($translation2->fresh()->is_preferred);
    }
}
