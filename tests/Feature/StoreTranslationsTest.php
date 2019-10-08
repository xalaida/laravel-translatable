<?php

namespace Nevadskiy\Translatable\Tests;

use Mockery;
use Nevadskiy\Translatable\Engine\TranslatorEngine;
use Nevadskiy\Translatable\Tests\Support\Models\Book;

class StoreTranslationsTest extends TestCase
{
    // TODO: check if attribute is translatable...
    // TODO: check if default locale
    // TODO: check if many attributes should only be from translatable array
    // TODO: test attribute mutators

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
    public function it_can_save_many_translations_for_translatable_attributes(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);

        $book->save();

        $book->translate([
            'title' => 'Тестовое название книги',
            'description' => 'Тестовое описание книги',
        ], 'ru');

        app()->setLocale('ru');

        $this->assertCount(2, $book->translations);
        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertEquals('Тестовое описание книги', $book->description);
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

    /** @test */
    public function it_can_use_translation_engine(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);

        $book->save();

        $engine = Mockery::mock(TranslatorEngine::class);

        $engine->shouldReceive('translate')
            ->with('Test book title', 'ru', 'en')
            ->andReturn('Тестовое название книги');

        $engine->shouldReceive('translate')
            ->with('Test book description', 'ru', 'en')
            ->andReturn('Тестовое описание книги');

        $this->app->instance(TranslatorEngine::class, $engine);

        $book->translateUsingEngine('ru');

        app()->setLocale('ru');

        $this->assertCount(2, $book->translations);
        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertEquals('Тестовое описание книги', $book->description);
    }
}

