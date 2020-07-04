<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AutoTranslationsSetTest extends TestCase
{
    /** @test */
    public function it_automatically_saves_translations_for_attributes_using_the_current_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $this->app->setLocale('ru');

        $book->title = 'Моя первая книга';
        $book->save();

        $book = $book->fresh();

        $this->assertCount(1, Translation::all());
        $this->assertEquals('Моя первая книга', $book->getTranslation('title', 'ru'));
        $this->assertEquals('My first book', $book->getDefaultAttribute('title'));
    }

    /** @test */
    public function it_saves_translations_using_update_method(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $this->app->setLocale('ru');

        $book->fillable(['title']);
        $book->update(['title' => 'Моя книга']);

        $book = $book->fresh();

        $this->assertCount(1, Translation::all());
        $this->assertEquals('Моя книга', $book->getTranslation('title', 'ru'));
        $this->assertEquals('My first book', $book->getDefaultAttribute('title'));
    }

    /** @test */
    public function it_does_not_store_translation_just_after_assignment(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $this->app->setLocale('ru');

        $book->title = 'Моя первая книга';

        $book = $book->fresh();

        $this->assertEmpty(Translation::all());
        $this->assertNull($book->getTranslation('title', 'ru'));
        $this->assertEquals('My first book', $book->title);
    }

    /** @test */
    public function it_overrides_previous_translations_correctly(): void
    {
        $book = BookFactory::new()->create(['title' => 'My book']);

        $book->translate('title', 'Ошибочное название книги', 'ru');

        $this->app->setLocale('ru');

        $book->title = 'Исправленное название книги';
        $book->save();

        $this->assertEquals('Исправленное название книги', $book->getTranslation('title'));
        $this->assertEquals('My book', $book->getDefaultAttribute('title'));
    }

    /** @test */
    public function it_does_not_store_resolved_values_as_translations_when_translations_not_available(): void
    {
        $book = BookFactory::new()->create(['title' => 'Not translatable title']);

        $this->app->setLocale('ru');

        $this->assertEquals('Not translatable title', $book->title);

        $book->save();

        $this->assertEmpty(Translation::all());
    }

    /** @test */
    public function it_saves_correctly_many_attributes_at_once(): void
    {
        $book = BookFactory::new()->create([
            'title' => 'My book',
            'description' => 'Book about birds',
        ]);

        $this->app->setLocale('ru');

        $book->title = 'Моя книга';
        $book->description = 'Книга о птицах';
        $book->version = 12;
        $book->save();

        $book = $book->fresh();

        $this->assertEquals('Моя книга', $book->title);
        $this->assertEquals('Книга о птицах', $book->description);
        $this->assertEquals(12, $book->version);

        $this->assertCount(2, Translation::all());
    }

    /** @test */
    public function it_handles_switching_locales_correctly(): void
    {
        $originalLocale = $this->app->getLocale();

        $book = BookFactory::new()->create(['title' => 'My original title']);
        $book->translate('title', 'Min ursprungliga titel', 'sv');
        $book->translate('title', 'Mi titulo original', 'es');

        $this->app->setLocale('sv');
        $this->assertEquals('Min ursprungliga titel', $book->title);

        $this->app->setLocale('es');
        $this->assertEquals('Mi titulo original', $book->title);

        $this->app->setLocale($originalLocale);
        $this->assertEquals('My original title', $book->title);
    }

    /** @test */
    public function it_does_not_save_translations_for_default_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->title = 'New test book title';
        $book->save();

        $this->assertEquals('New test book title', $book->title);
        $this->assertEmpty(Translation::all());
    }

    /** @test */
    public function it_does_not_save_translations_for_not_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $this->app->setLocale('ru');

        $book->version = 3;
        $book->save();

        $book = $book->fresh();

        $this->assertEquals(3, $book->version);
        $this->assertEmpty(Translation::all());
    }

    /** @test */
    public function it_does_not_save_null_values(): void
    {
        $book = BookFactory::new()->create(['description' => 'Book about animals']);

        $this->app->setLocale('ru');

        $book->description = null;
        $book->save();

        $book = $book->fresh();

        $this->assertEquals('Book about animals', $book->description);
        $this->assertEmpty(Translation::all());
    }
}
