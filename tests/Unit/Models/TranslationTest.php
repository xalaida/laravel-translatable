<?php

namespace Nevadskiy\Translatable\Tests\Unit\Models;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Factories\TranslationFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class TranslationTest extends TestCase
{
    /** @test */
    public function it_has_is_archived_attribute(): void
    {
        $book = BookFactory::new()->create();

        $translation = TranslationFactory::new()->for($book, 'title')->create([
            'is_archived' => true,
        ]);

        self::assertTrue($translation->fresh()->is_archived);
    }

    /** @test */
    public function it_can_be_scoped_by_locale(): void
    {
        $book = BookFactory::new()->create();

        $translation1 = TranslationFactory::new()
            ->for($book, 'title')
            ->locale('ru')
            ->create();

        $translation2 = TranslationFactory::new()
            ->for($book, 'title')
            ->locale('es')
            ->create();

        $translations = Translation::query()->forLocale('ru')->get();

        self::assertCount(1, $translations);
        self::assertTrue($translations->first()->is($translation1));
    }

    /** @test */
    public function it_can_be_scoped_by_attribute(): void
    {
        $book = BookFactory::new()->create();

        $translation1 = TranslationFactory::new()
            ->for($book, 'title')
            ->create();

        $translation2 = TranslationFactory::new()
            ->for($book, 'description')
            ->create();

        $translations = Translation::query()->forAttribute('title')->get();

        self::assertCount(1, $translations);
        self::assertTrue($translations->first()->is($translation1));
    }
}
