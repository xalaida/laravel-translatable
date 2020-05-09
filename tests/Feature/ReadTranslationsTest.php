<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use DB;
use Nevadskiy\Translatable\Tests\Support\Models\Block;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class ReadTranslationsTest extends TestCase
{
    /** @test */
    public function it_successfully_retrieve_translatable_attribute(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertEquals('Тестовое название книги', $book->fresh()->title);
    }

    /** @test */
    public function it_does_not_break_model_on_save_after_translation(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');
        $this->assertEquals('Тестовое название книги', $book->title);

        $book->save();

        app()->setLocale('en');
        $book = $book->fresh();
        $this->assertEquals('Test book title', $book->title);
    }

    /** @test */
    public function it_returns_default_value_if_translation_is_not_exist(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        app()->setLocale('ru');

        $this->assertEquals('Test book title', $book->fresh()->title);
    }

    /** @test */
    public function it_returns_correct_translation_within_different_locales(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Título del libro',
            'locale' => 'es',
        ]);

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Titolo del libro',
            'locale' => 'it',
        ]);

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->fresh()->title);
    }

    /** @test */
    public function it_returns_correct_translation_within_different_attributes(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        $book->translations()->create([
            'translatable_attribute' => 'description',
            'translatable_value' => 'Тестовое описание книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $this->assertEquals('Тестовое описание книги', $book->fresh()->description);
    }

    /** @test */
    public function it_returns_original_value_if_default_locale_is_set(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Wrong set title translation',
            'locale' => 'en',
        ]);

        app()->setLocale(config('app.fallback_locale'));

        $this->assertEquals('Test book title', $book->fresh()->title);
    }

    /** @test */
    public function it_performs_only_two_queries_for_translations_eager_loading(): void
    {
        foreach (range(0, 10) as $i) {
            $book = new Book([
                'title' => "Test book title {$i}",
                'description' => "Test book description {$i}",
            ]);

            $book->save();

            $book->translations()->create([
                'translatable_attribute' => 'title',
                'translatable_value' => "Тестовое название книги {$i}",
                'locale' => 'ru',
            ]);

            $book->translations()->create([
                'translatable_attribute' => 'description',
                'translatable_value' => "Тестовое описание книги {$i}",
                'locale' => 'ru',
            ]);
        }

        DB::enableQueryLog();

        app()->setLocale('ru');

        Book::all()->each(function ($book, $i) {
            $this->assertEquals("Тестовое название книги {$i}", $book->title);
            $this->assertEquals("Тестовое описание книги {$i}", $book->description);
        });

        $this->assertCount(2, DB::getQueryLog());
    }

    /** @test */
    public function it_works_correctly_with_accessors(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        $book->translations()->create([
            'translatable_attribute' => 'description',
            'translatable_value' => 'Тестовое описание книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $this->assertEquals('Тес...', $book->fresh()->description_short);
    }

    /** @test */
    public function it_successfully_works_with_accessors(): void
    {
        $book = new Book([
            'title' => 'test book title',
            'description' => 'Test book description',
        ]);

        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'тестовое название книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->fresh()->title);
    }

    /** @test */
    public function it_returns_null_for_unknown_attribute(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        app()->setLocale('ru');

        $this->assertNull($book->fresh()->color);
    }

    /** @test */
    public function it_returns_original_value_for_not_translatable_attribute(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        $this->assertEquals(1, $book->id);
    }

    /** @test */
    public function it_has_translated_attributes_after_to_array_converting(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $bookArray = $book->toArray();

        $this->assertEquals('Тестовое название книги', $bookArray['title']);
        $this->assertEquals('Test book description', $bookArray['description']);
    }

    /** @test */
    public function it_store_original_attribute_value_after_translation(): void
    {
        $book = new Book([
            'title' => 'Test book title',
            'description' => 'Test book description',
        ]);
        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertEquals('Test book title', $book->getWithoutTranslation('title'));
    }

    /** @test */
    public function it_returns_original_before_translation_value_with_accessor_applied(): void
    {
        $book = new Book([
            'title' => 'test book title',
            'description' => 'Test book description',
        ]);

        $book->save();

        $book->translations()->create([
            'translatable_attribute' => 'title',
            'translatable_value' => 'Тестовое название книги',
            'locale' => 'ru',
        ]);

        app()->setLocale('ru');

        $this->assertEquals('Тестовое название книги', $book->title);
        $this->assertEquals('Test book title', $book->getWithoutTranslation('title'));
    }

    /** @test */
    public function it_successfully_casts_translatable_attribute(): void
    {
        $block = new Block([
            'type' => 'comment',
            'body' => ['title' => 'The block title', 'content' => 'The block content'],
        ]);
        $block->save();

        $block->translate(['body' => ['title' => 'Заголовок блока', 'content' => 'Содержание блока']], 'ru');

        app()->setLocale('ru');

        $this->assertEquals([
            'title' => 'Заголовок блока',
            'content' => 'Содержание блока'
        ], $block->body);
    }

    /** @test */
    public function it_successfully_casts_attribute_which_have_no_translations_available(): void
    {
        $block = new Block([
            'type' => 'comment',
            'body' => ['title' => 'The block title', 'content' => 'The block content'],
        ]);
        $block->save();

        app()->setLocale('ru');

        $this->assertEquals(['title' => 'The block title', 'content' => 'The block content'], $block->body);
    }
}
