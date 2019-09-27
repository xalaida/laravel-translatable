<?php

namespace Nevadskiy\Translatable\Tests\Unit;

use Mockery;
use Nevadskiy\Translatable\AutoTranslator;
use Nevadskiy\Translatable\Engine\TranslatorEngine;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class AutoTranslatorTest extends TestCase
{
    /** @test */
    public function it_translate_models_using_translator_engine(): void
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

        $translator = app(AutoTranslator::class);

        $translator->translate($book, 'ru');

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertEquals('Тестовое описание книги', $book->description);
    }
}
