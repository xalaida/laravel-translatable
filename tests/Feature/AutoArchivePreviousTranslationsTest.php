<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AutoArchivePreviousTranslationsTest extends TestCase
{
    // TODO: do not store same translations as archived (unarchive previous or what...)

    // TODO: auto archive translations for default locale when overrides default attribute
    // TODO: save archived translations directly
    // TODO: check if search works for archived translations
    // TODO: feature disabling archived translations and only override current or do nothing
    // TODO: do not use it by default
    // TODO: when new translation is added - archive others
    // TODO: archived translations does not resolves automatically
    // TODO: add method for retrieving archived translations
    // TODO: test nullable values
    // TODO: it_switches_previous_preferred_values_to_not_preferred is set feature is enabled

    // TODO: it does not archive for different locale
    // TODO: it does not archive for different attribute
    // TODO: it does not archive for different model

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

        $book->enableAutoArchiveTranslations();

        $book->translate('title', 'Старое название книги', 'ru');
        $book->translate('title', 'Новое название книги', 'ru');

        $translations = Translation::all();

        self::assertCount(2, $translations);
        self::assertTrue($translations[0]->is_archived);
        self::assertFalse($translations[1]->is_archived);
        self::assertEquals('Новое название книги', $translations[1]->value);
        self::assertEquals('Новое название книги', $book->getTranslation('title', 'ru'));
    }
}
