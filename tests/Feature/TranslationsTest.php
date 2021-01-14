<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Exceptions\NotTranslatableAttributeException;
use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class TranslationsTest extends TestCase
{
    /** @test */
    public function it_saves_translations_for_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Моя тестовая книга', 'ru');

        self::assertEquals('Моя тестовая книга', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_returns_correct_value_from_multiple_translations(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Моя блестящая книга', 'ru');
        $book->translate('title', 'Mi brillante libro', 'es');
        $book->translate('title', 'Mon brillant livre', 'fr');

        self::assertEquals('Mi brillante libro', $book->getTranslation('title', 'es'));
    }

    /** @test */
    public function it_does_not_break_anything_for_default_attributes(): void
    {
        $book = BookFactory::new()->create(['version' => 24]);

        self::assertEquals(24, $book->version);
    }

    /** @test */
    public function it_returns_correct_value_from_multiple_attributes(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Книга о птицах', 'ru');
        $book->translate('description', 'Livre sur les oiseaux', 'fr');

        self::assertEquals('Книга о птицах', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_does_not_override_default_values(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        $book->translate('title', 'Моя оригинальная книга', 'ru');

        $book = $book->fresh();

        self::assertEquals('Моя оригинальная книга', $book->getTranslation('title', 'ru'));
        self::assertEquals('My original book', $book->title);
    }

    /** @test */
    public function it_returns_null_if_translation_does_not_exist(): void
    {
        self::assertNull(
            BookFactory::new()->create()->getTranslation('title', 'fr')
        );
    }

    /** @test */
    public function it_returns_default_value_correctly_if_translation_does_not_exist(): void
    {
        $book = BookFactory::new()->create(['title' => 'English title']);

        self::assertEquals('English title', $book->getAttribute('title'));
    }

    /** @test */
    public function it_saves_translations_to_the_database(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Моя новая книга', 'ru');

        self::assertCount(1, Translation::all());

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->id,
            'translatable_type' => $book->getMorphClass(),
            'translatable_attribute' => 'title',
            'value' => 'Моя новая книга',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_saves_many_translations_for_translatable_attributes(): void
    {
        $book = BookFactory::new()->create();

        $book->translateMany([
            'title' => 'Тестовое название книги',
            'description' => 'Тестовое описание книги',
        ], 'ru');

        self::assertEquals('Тестовое название книги', $book->getTranslation('title', 'ru'));
        self::assertEquals('Тестовое описание книги', $book->getTranslation('description', 'ru'));
    }

    /** @test */
    public function it_saves_many_translations_to_the_database(): void
    {
        $book = BookFactory::new()->create();

        $book->translateMany([
            'title' => 'Моя новая книга',
            'description' => 'Как хранить переводы для Laravel',
        ], 'ru');

        self::assertCount(2, Translation::all());

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->id,
            'translatable_type' => $book->getMorphClass(),
            'translatable_attribute' => 'title',
            'value' => 'Моя новая книга',
            'locale' => 'ru',
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->id,
            'translatable_type' => $book->getMorphClass(),
            'translatable_attribute' => 'description',
            'value' => 'Как хранить переводы для Laravel',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_returns_translation_from_different_locales(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original title']);

        $book->translate('title', 'Min ursprungliga titel', 'sv');
        $book->translate('title', 'Mi titulo original', 'es');

        self::assertEquals('Min ursprungliga titel', $book->getTranslation('title', 'sv'));
        self::assertEquals('Mi titulo original', $book->getTranslation('title', 'es'));
        self::assertEquals('My original title', $book->title);
    }

    /** @test */
    public function it_overrides_previous_translations(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Неправильное название книги', 'ru');
        $book->translate('title', 'Правильное название книги', 'ru');

        self::assertCount(1, Translation::all());
        self::assertEquals('Правильное название книги', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_does_not_translate_using_nulls(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('description', null, 'ru');

        self::assertCount(0, Translation::all());
    }

    /** @test */
    public function it_updates_default_value_for_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My book']);

        $book->translate('title', 'My english book', $this->app->getLocale());

        self::assertEquals('My english book', $book->getAttribute('title'));
        self::assertEmpty(Translation::all());
    }

    /** @test */
    public function it_throws_en_exception_during_translation_not_translatable_attributes(): void
    {
        $book = BookFactory::new()->create(['title' => 'My book']);

        try {
            $book->translate('version', '5', $this->app->getLocale());
            self::fail('Exception was not thrown for not translatable attribute');
        } catch (NotTranslatableAttributeException $e) {
            self::assertCount(0, Translation::all());
        }
    }
}
