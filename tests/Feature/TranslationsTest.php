<?php

namespace Nevadskiy\Translatable\Tests\Feature;

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

        $this->assertEquals('Моя тестовая книга', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_returns_correct_value_from_multiple_translations(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Моя блестящая книга', 'ru');
        $book->translate('title', 'Mi brillante libro', 'es');
        $book->translate('title', 'Mon brillant livre', 'fr');

        $this->assertEquals('Mi brillante libro', $book->getTranslation('title', 'es'));
    }

    /** @test */
    public function it_does_not_break_anything_for_default_attributes(): void
    {
        $book = BookFactory::new()->create(['version' => 24]);

        $this->assertEquals(24, $book->version);
    }

    /** @test */
    public function it_returns_correct_value_from_multiple_attributes(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Книга о птицах', 'ru');
        $book->translate('description', 'Livre sur les oiseaux', 'fr');

        $this->assertEquals('Книга о птицах', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_does_not_override_default_values(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        $book->translate('title', 'Моя оригинальная книга', 'ru');

        $book = $book->fresh();

        $this->assertEquals('Моя оригинальная книга', $book->getTranslation('title', 'ru'));
        $this->assertEquals('My original book', $book->title);
    }

    /** @test */
    public function it_returns_null_if_translation_does_not_exist(): void
    {
        $this->assertNull(
            BookFactory::new()->create()->getTranslation('title', 'fr')
        );
    }

    /** @test */
    public function it_returns_default_value_correctly_if_translation_does_not_exist(): void
    {
        $book = BookFactory::new()->create(['title' => 'English title']);

        $this->assertEquals('English title', $book->getAttribute('title'));
    }

    /** @test */
    public function it_saves_translations_to_the_database(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Моя новая книга', 'ru');

        $this->assertCount(1, Translation::all());

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

        $this->assertEquals('Тестовое название книги', $book->getTranslation('title', 'ru'));
        $this->assertEquals('Тестовое описание книги', $book->getTranslation('description', 'ru'));
    }

    /** @test */
    public function it_saves_many_translations_to_the_database(): void
    {
        $book = BookFactory::new()->create();

        $book->translateMany([
            'title' => 'Моя новая книга',
            'description' => 'Как хранить переводы для Laravel',
        ], 'ru');

        $this->assertCount(2, Translation::all());

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

        $this->assertEquals('Min ursprungliga titel', $book->getTranslation('title', 'sv'));
        $this->assertEquals('Mi titulo original', $book->getTranslation('title', 'es'));
        $this->assertEquals('My original title', $book->title);
    }

    /** @test */
    public function it_overrides_previous_translations(): void
    {
        $book = BookFactory::new()->create();

        $book->translate('title', 'Неправильное название книги', 'ru');
        $book->translate('title', 'Правильное название книги', 'ru');

        $this->assertCount(1, Translation::all());
        $this->assertEquals('Правильное название книги', $book->getTranslation('title', 'ru'));
    }

    /** @test */
    public function it_saves_translations_correctly_even_for_default_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My book']);

        $book->translate('title', 'My english book', $this->app->getLocale());

        $this->assertEquals('My english book', $book->getTranslation('title', $this->app->getLocale()));
        $this->assertCount(1, Translation::all());
    }

    /** @test */
    public function it_saves_translations_correctly_even_for_not_translatable_attributes(): void
    {
        $book = BookFactory::new()->create(['title' => 'My book']);

        $book->translate('version', '5', $this->app->getLocale());

        $this->assertEquals('5', $book->getTranslation('version', $this->app->getLocale()));
        $this->assertCount(1, Translation::all());
    }

    /** @test */
    public function it_returns_translation_model_from_translate_method(): void
    {
        $book = BookFactory::new()->create();

        $translation = $book->translate('title', 'Моя книга', 'ru');

        $this->assertEquals('Моя книга', $translation->value);
        $this->assertEquals('ru', $translation->locale);
        $this->assertEquals('title', $translation->translatable_attribute);
        $this->assertTrue($translation->translatable->is($book));
    }

    /** @test */
    public function it_returns_translations_collection_from_translate_many_method(): void
    {
        $book = BookFactory::new()->create();

        $translations = $book->translateMany(['title' => 'Моя книга', 'description' => 'Мое описание'], 'ru');

        $this->assertCount(2, $translations);

        $this->assertEquals('Моя книга', $translations[0]->value);
        $this->assertEquals('title', $translations[0]->translatable_attribute);
        $this->assertEquals('ru', $translations[0]->locale);
        $this->assertTrue($translations[0]->translatable->is($book));

        $this->assertEquals('Мое описание', $translations[1]->value);
        $this->assertEquals('description', $translations[1]->translatable_attribute);
        $this->assertEquals('ru', $translations[1]->locale);
        $this->assertTrue($translations[1]->translatable->is($book));
    }
}
