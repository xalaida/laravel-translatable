<?php

namespace Nevadskiy\Translatable\Tests;

use Carbon\Carbon;
use Mockery;
use Nevadskiy\Translatable\Engine\TranslatorEngine;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Translation;

class StoreTranslationsTest extends TestCase
{
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

    /** @test */
    public function it_use_model_mutation_for_attributes(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translate(['title' => 'Очень очень длинное название для книги'], 'ru');

        $this->assertEquals('Test book title', $book->title);

        app()->setLocale('ru');

        $this->assertEquals('Очень очень длинное название д...', $book->title);
    }

    /** @test */
    public function it_restores_original_attribute_after_applied_mutations(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $originalAttributes = $book->getAttributes();

        $book->translate(['title' => 'Очень очень длинное название для книги'], 'ru');

        $this->assertEquals($originalAttributes, $book->getAttributes());
    }

    /** @test */
    public function it_sets_attribute_value_correctly_for_translatable_attributes(): void
    {
        $book = new Book([
            'title' => 'test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translate(['title' => 'Тестовое название книги'], 'ru');

        app()->setLocale('ru');

        $book->title = 'Новое название книги';

        $this->assertEquals('Новое название книги', $book->title);
        $this->assertEquals('test book title', $book->getAttributes()['title']);
    }

    /** @test */
    public function it_sets_attribute_value_correctly_for_not_translatable_attributes(): void
    {
        $book = new Book([
            'title' => 'test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        app()->setLocale('ru');

        $timestamp = Carbon::createFromTimestamp(Carbon::now()->subMonth()->getTimestamp());

        $book->created_at = $timestamp;

        $this->assertEquals($timestamp, $book->created_at);
        $this->assertEquals($timestamp, $book->getAttributes()['created_at']);
    }

    /** @test */
    public function it_sets_translatable_attribute_value_correctly_for_default_locale(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->title = 'New test book title';

        $this->assertEquals('New test book title', $book->title);
        $this->assertEquals('New test book title', $book->getAttributes()['title']);
    }

    /** @test */
    public function it_stores_translation_for_translatable_attribute_on_model_saving(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        app()->setLocale('ru');

        $book->title = 'Новая книга';
        $book->save();

        $this->assertEquals('Новая книга', $book->title);

        $this->assertDatabaseHas('books', [
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_attribute' => 'title',
            'translatable_value' => 'Новая книга',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_stores_translation_for_translatable_attribute_on_model_updating(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        app()->setLocale('ru');

        $book->update([
            'title' => 'Новая книга'
        ]);

        $this->assertEquals('Новая книга', $book->fresh()->title);

        $this->assertDatabaseHas('books', [
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_attribute' => 'title',
            'translatable_value' => 'Новая книга',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_does_not_store_default_values_as_translatable_when_translations_not_available(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        app()->setLocale('ru');

        $this->assertEquals('Test book title', $book->title);
        $this->assertEquals('Test book description', $book->description);

        $book->save();

        $this->assertEmpty(Translation::all());
    }
}
