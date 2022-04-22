<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class SetterTranslationTest extends TestCase
{
    /** @test */
    public function it_automatically_saves_translations_for_attributes_using_current_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $this->app->setLocale('ru');

        $book->title = 'Моя первая книга';
        $book->save();

        $book = $book->fresh();

        self::assertEquals('Моя первая книга', $book->translator()->get('title', 'ru'));
        self::assertEquals('My first book', $book->getOriginalAttribute('title'));
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_saves_translations_using_update_method(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $this->app->setLocale('ru');

        $book->fillable(['title']);
        $book->update(['title' => 'Моя книга']);

        $book = $book->fresh();

        self::assertEquals('Моя книга', $book->translator()->get('title', 'ru'));
        self::assertEquals('My first book', $book->getOriginalAttribute('title'));
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_store_translation_just_after_assignment(): void
    {
        $book = BookFactory::new()->create(['title' => 'My first book']);

        $this->app->setLocale('ru');

        $book->title = 'Моя первая книга';

        $book = $book->fresh();

        self::assertNull($book->translator()->get('title', 'ru'));
        self::assertEquals('My first book', $book->title);
        $this->assertDatabaseCount('translations', 0);
    }

    /** @test */
    public function it_overrides_previous_translations_correctly(): void
    {
        $book = BookFactory::new()->create(['title' => 'My book']);

        $book->translator()->add('title', 'Ошибочное название книги', 'ru');

        $this->app->setLocale('ru');

        $book->title = 'Исправленное название книги';
        $book->save();

        self::assertEquals('Исправленное название книги', $book->translator()->get('title'));
        self::assertEquals('My book', $book->getOriginalAttribute('title'));
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_store_resolved_values_as_translations_when_translations_not_available(): void
    {
        $book = BookFactory::new()->create(['title' => 'Not translatable title']);

        $this->app->setLocale('ru');

        self::assertEquals('Not translatable title', $book->title);

        $book->save();

        $this->assertDatabaseCount('translations', 0);
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

        self::assertEquals('Моя книга', $book->title);
        self::assertEquals('Книга о птицах', $book->description);
        self::assertEquals(12, $book->version);

        $this->assertDatabaseCount('translations', 2);
    }

    /** @test */
    public function it_handles_switching_locales_correctly(): void
    {
        $originalLocale = $this->app->getLocale();

        $book = BookFactory::new()->create(['title' => 'My original title']);
        $book->translator()->set('title', 'Min ursprungliga titel', 'sv');
        $book->translator()->set('title', 'Mi titulo original', 'es');

        $this->app->setLocale('sv');
        self::assertEquals('Min ursprungliga titel', $book->title);

        $this->app->setLocale('es');
        self::assertEquals('Mi titulo original', $book->title);

        $this->app->setLocale($originalLocale);
        self::assertEquals('My original title', $book->title);
    }

    /** @test */
    public function it_does_not_save_translations_for_default_locale(): void
    {
        $book = BookFactory::new()->create();

        $book->title = 'New test book title';
        $book->save();

        self::assertEquals('New test book title', $book->title);
        $this->assertDatabaseCount('translations', 0);
    }

    /** @test */
    public function it_does_not_save_translations_for_non_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $this->app->setLocale('ru');

        $book->version = 3;
        $book->save();

        $book = $book->fresh();

        self::assertEquals(3, $book->version);
        $this->assertDatabaseCount('translations', 0);
    }

    /** @test */
    public function it_does_not_save_null_values(): void
    {
        $book = BookFactory::new()->create(['description' => 'Book about animals']);

        $this->app->setLocale('ru');

        $book->description = null;
        $book->save();

        $book = $book->fresh();

        self::assertEquals('Book about animals', $book->description);
        $this->assertDatabaseCount('translations', 0);
    }

    /** @test */
    public function it_does_not_store_pending_translations_twice(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);

        $this->app->setLocale('ru');

        $book->title = 'Моя лучшая книга';
        $book->save();

        DB::connection()->enableQueryLog();

        $book->save();

        self::assertEmpty(DB::connection()->getQueryLog());
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_does_not_duplicate_translations(): void
    {
        $book = BookFactory::new()->create(['title' => 'My best book']);

        $this->app->setLocale('ru');

        $book->title = 'Моя лучшая книга';
        $book->save();

        $book->title = 'Моя лучшая книга';
        $book->save();

        $this->assertDatabaseCount('translations', 1);
    }
}
