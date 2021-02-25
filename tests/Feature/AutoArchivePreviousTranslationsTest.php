<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AutoArchivePreviousTranslationsTest extends TestCase
{
    /** @test */
    public function it_does_not_archive_previous_translations_by_default(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Старое название книги', 'ru');
        $book->translate('title', 'Новое название книги', 'ru');

        self::assertCount(1, Translation::all());
    }

    /** @test */
    public function it_can_automatically_archive_previous_translations(): void
    {
        $book = BookFactory::new()->create();

        $book->enableArchivingTranslations();

        $book->translate('title', 'Старое название книги', 'ru');
        $book->translate('title', 'Новое название книги', 'ru');

        $translations = Translation::all();

        self::assertCount(2, $translations);
        self::assertTrue($translations[0]->is_archived);
        self::assertFalse($translations[1]->is_archived);
        self::assertEquals('Новое название книги', $translations[1]->value);
        self::assertEquals('Новое название книги', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_can_automatically_archive_translations_on_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'Original title']);

        $book->enableArchivingTranslations();
        $this->app->setLocale('en');

        $book->title = 'Updated title';
        $book->save();

        $translations = Translation::all();

        self::assertCount(1, $translations);
        self::assertTrue($translations[0]->is_archived);
        self::assertEquals('Original title', $translations[0]->value);
        self::assertEquals('en', $translations[0]->locale);
        self::assertEquals('Updated title', $book->title);
    }

    /** @test */
    public function it_does_not_automatically_archive_translations_on_default_locale_after_assignment(): void
    {
        $book = BookFactory::new()->create(['title' => 'Original title']);

        $book->enableArchivingTranslations();
        $this->app->setLocale('en');

        $book->title = 'Updated title';

        self::assertEmpty(Translation::all());
    }

    /** @test */
    public function it_does_not_archive_previous_translations_for_another_attribute(): void
    {
        $book = BookFactory::new()->create();

        $book->enableArchivingTranslations();

        $book->translate('title', 'Название книги', 'ru');
        $book->translate('description', 'Книга про животных', 'ru');

        $translations = Translation::all();

        self::assertCount(2, $translations);
        self::assertFalse($translations[0]->is_archived);
        self::assertFalse($translations[1]->is_archived);
    }

    /** @test */
    public function it_does_not_archive_previous_translations_for_another_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->enableArchivingTranslations();

        $book->translate('title', 'Название книги', 'ru');
        $book->translate('title', 'Book title', 'es');

        $translations = Translation::all();

        self::assertCount(2, $translations);
        self::assertFalse($translations[0]->is_archived);
        self::assertFalse($translations[1]->is_archived);
    }

    /** @test */
    public function it_does_not_archive_previous_translations_for_another_model(): void
    {
        $book1 = BookFactory::new()->create();
        $book2 = BookFactory::new()->create();

        $book1->enableArchivingTranslations();
        $book2->enableArchivingTranslations();

        $book1->translate('title', 'Книга о животных', 'ru');
        $book2->translate('title', 'Книга о растениях', 'ru');

        $translations = Translation::all();

        self::assertCount(2, $translations);
        self::assertFalse($translations[0]->is_archived);
        self::assertFalse($translations[1]->is_archived);
    }
}
