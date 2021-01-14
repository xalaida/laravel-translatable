<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Exceptions\NotTranslatableAttributeException;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AddArchivedTranslationsTest extends TestCase
{
    /** @test */
    public function it_adds_archived_translations(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $book->translate('title', 'Моя первая книга', 'ru');

        $archivedTranslation = $book->archiveTranslation('title', 'Книга #1', 'ru');

        self::assertCount(2, $book->translations);
        self::assertEquals('Моя первая книга', $book->getTranslation('title', 'ru'));
        self::assertEquals('title', $archivedTranslation->translatable_attribute);
        self::assertEquals('ru', $archivedTranslation->locale);
        self::assertEquals('Книга #1', $archivedTranslation->value);
    }

    /** @test */
    public function it_does_not_resolve_archived_translations(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $book->archiveTranslation('title', 'Моя книга', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('My first book', $book->title);
        self::assertEquals('My first book', $book->getTranslationOrDefault('title', 'ru'));
    }

    /** @test */
    public function it_throws_an_exception_for_not_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $this->expectException(NotTranslatableAttributeException::class);

        $book->archiveTranslation('version', 5, 'ru');
    }

    /** @test */
    public function it_allows_adding_archived_translations_with_nullable_locale_when_the_locale_is_unknown(): void
    {
        $book = BookFactory::new()->create();

        $translation = $book->archiveTranslation('title', 'lorem ipsum', null);

        self::assertCount(1, Translation::all());
        self::assertNull($translation->locale);
        self::assertTrue($translation->is_archived);
        self::assertEquals('title', $translation->translatable_attribute);
        self::assertEquals('lorem ipsum', $translation->value);
    }

    /** @test */
    public function it_can_add_archived_translations_using_current_locale(): void
    {
        $book = BookFactory::new()->create();

        $this->app->setLocale('la');

        $translation = $book->archiveTranslation('title', 'lorem ipsum');

        self::assertCount(1, Translation::all());
        self::assertEquals('la', $translation->locale);
        self::assertTrue($translation->is_archived);
        self::assertEquals('title', $translation->translatable_attribute);
        self::assertEquals('lorem ipsum', $translation->value);
    }

    /** @test */
    public function it_can_add_archived_translations_for_default_locale(): void
    {
        $book = BookFactory::new()->create();

        $translation = $book->archiveTranslation('title', 'lorem ipsum', 'en');

        self::assertCount(1, Translation::all());
        self::assertEquals('en', $translation->locale);
        self::assertTrue($translation->is_archived);
    }

    /** @test */
    public function it_archives_translation_applying_attribute_mutators(): void
    {
        $book = BookFactory::new()->create();

        $translation = $book->archiveTranslation('title', 'Очень очень длинное название для книги', 'ru');

        self::assertEquals('Очень очень длинное название д...', $translation->value);
    }

    /** @test */
    public function it_does_not_duplicate_archived_translations(): void
    {
        $book = BookFactory::new()->create();

        $book->archiveTranslation('title', 'Моя книга', 'ru');
        $book->archiveTranslation('title', 'Моя книга', 'ru');

        self::assertCount(1, Translation::all());
    }
}
