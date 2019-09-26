<?php

namespace Nevadskiy\Translatable\Tests;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

class ReadTranslationsTest extends TestCase
{
    // TODO: unknown attribute
    // TODO: test accessors

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

        $this->assertEquals('Тестовое название книги', $book->fresh()->title);
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
}

/**
 * TODO: extract to support model class
 *
 * @property string title
 * @property string description
 */
class Book extends Model
{
    use HasTranslations;

    protected $guarded = [];

    protected $translatable = ['title', 'description'];
}
