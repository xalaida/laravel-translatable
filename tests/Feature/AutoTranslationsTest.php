<?php

namespace Nevadskiy\Translatable\Tests;

use Mockery;
use Nevadskiy\Translatable\Engine\TranslatorEngine;
use Nevadskiy\Translatable\Tests\Support\Models\Book;

class AutoTranslationsTest extends TestCase
{
    /** @test */
    public function it_can_be_translated_using_engine(): void
    {
        $engine = Mockery::mock(TranslatorEngine::class);

        $engine->shouldReceive('translate')
            ->with('Test book title', 'ru', 'en')
            ->andReturn('Тестовое название книги');

        $engine->shouldReceive('translate')
            ->with('Test book description', 'ru', 'en')
            ->andReturn('Тестовое описание книги');

        $this->app->instance(TranslatorEngine::class, $engine);

        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);

        $book->save();

        $book->translateUsingEngine('ru');

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertEquals('Тестовое описание книги', $book->description);
    }
}

