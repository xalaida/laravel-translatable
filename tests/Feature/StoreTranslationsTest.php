<?php

namespace Nevadskiy\Translatable\Tests;

use Nevadskiy\Translatable\Engine\GoogleTranslateEngine;
use Nevadskiy\Translatable\Engine\TranslatorEngine;
use Nevadskiy\Translatable\Tests\Support\Models\Book;

class StoreTranslationsTest extends TestCase
{
    // TODO: add possibility to save many translations
    // TODO: check if attribute is translatable...
    // TODO: check if default locale
    // TODO: check if many attributes should only be from translatable array

    /** @test */
    public function it_can_save_translations_for_translatable_attributes(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);

        $book->save();

        $book->translate(['title' => 'Тестовое название книги'], 'ru');

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->title);
    }

    /** @test */
    public function it_updates_previous_translation_on_second_translate(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);

        $book->save();

        $book->translate(['title' => 'Неправильный перевод'], 'ru');

        $book->translate(['title' => 'Тестовое название книги'], 'ru');

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertCount(1, $book->translations);
    }

//    /** @test */
//    public function it_google_translate(): void
//    {
//        $t = app(TranslatorEngine::class);
//
//        dd($t->translate('Test book title', 'ru', 'en'));
//    }
}

