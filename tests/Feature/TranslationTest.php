<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Strategies\Single\Models\Translation;
use Nevadskiy\Translatable\Exceptions\AttributeNotTranslatableException;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

// TODO: update these tests to use mock strategy
class TranslationTest extends TestCase
{
//    /** @test */
//    public function it_handles_translations_for_translatable_attributes(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->add('title', 'Моя первая книга', 'uk');
//
//        self::assertEquals('Моя первая книга', $book->translator()->get('title', 'uk'));
//    }

//    /** @test */
//    public function it_retrieves_correct_value_from_multiple_translations(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->add('title', 'Книга о птицах', 'uk');
//        $book->translator()->add('description', 'Livre sur les oiseaux', 'fr');
//
//        self::assertEquals('Книга о птицах', $book->translator()->get('title', 'uk'));
//    }

//    /** @test */
//    public function it_retrieves_translation_for_specified_locale(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->add('title', 'Моя блестящая книга', 'uk');
//        $book->translator()->add('title', 'Mi brillante libro', 'es');
//        $book->translator()->add('title', 'Mon brillant livre', 'fr');
//
//        self::assertEquals('Mi brillante libro', $book->translator()->get('title', 'es'));
//    }
//
//    /** @test */
//    public function it_retrieves_original_value_for_fallback_locale(): void
//    {
//        $book = BookFactory::new()->create(['title' => 'My best book']);
//        $book->translator()->add('title', 'Моя лучшая книга', 'uk');
//
//        self::assertEquals('My best book', $book->translator()->get('title', 'en'));
//    }

//    /** @test */
//    public function it_throws_exception_when_trying_to_get_translation_for_non_translatable_attribute(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $this->expectException(AttributeNotTranslatableException::class);
//
//        $book->translator()->get('id');
//    }

//    /** @test */
//    public function it_updates_original_attribute_for_fallback_locale(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->add('title', 'Book title in English', 'en');
//
//        self::assertEquals('Book title in English', $book->title);
//        self::assertEmpty(Translation::all());
//    }

//    /** @test */
//    public function it_throws_exception_when_trying_to_add_translation_for_non_translatable_attribute(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $this->expectException(AttributeNotTranslatableException::class);
//
//        $book->translator()->add('id', 'Spanish ID', 'es');
//    }
//
//    /** @test */
//    public function it_does_not_break_anything_for_default_attributes(): void
//    {
//        $book = BookFactory::new()->create(['version' => 24]);
//
//        self::assertEquals(24, $book->version);
//    }

//    /** @test */
//    public function it_does_not_override_original_values(): void
//    {
//        $book = BookFactory::new()->create(['title' => 'My original book']);
//
//        $book->translator()->add('title', 'Моя оригинальная книга', 'uk');
//
//        $book = $book->fresh();
//
//        self::assertEquals('Моя оригинальная книга', $book->translator()->get('title', 'uk'));
//        self::assertEquals('My original book', $book->title);
//    }

//    /** @test */
//    public function it_returns_null_if_translation_does_not_exist(): void
//    {
//        self::assertNull(
//            BookFactory::new()->create()->translator()->get('title', 'fr')
//        );
//    }

//    /** @test */
//    public function it_returns_fallback_value_if_translation_does_not_exist(): void
//    {
//        $book = BookFactory::new()->create(['title' => 'English title']);
//
//        self::assertEquals('English title', $book->translator()->getOrFallback('title', 'ua'));
//    }

    // TODO: probably move to strategy specific test.

//    /** @test */
//    public function it_saves_translations_to_database(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->add('title', 'Моя новая книга', 'uk');
//
//        self::assertCount(1, Translation::all());
//
//        $this->assertDatabaseHas('translations', [
//            'translatable_id' => $book->id,
//            'translatable_type' => $book->getMorphClass(),
//            'translatable_attribute' => 'title',
//            'value' => 'Моя новая книга',
//            'locale' => 'uk',
//        ]);
//    }
//
//    /** @test */
//    public function it_saves_many_translations_for_translatable_attributes(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->addMany([
//            'title' => 'Тестовое название книги',
//            'description' => 'Тестовое описание книги',
//        ], 'uk');
//
//        self::assertEquals('Тестовое название книги', $book->translator()->get('title', 'uk'));
//        self::assertEquals('Тестовое описание книги', $book->translator()->get('description', 'uk'));
//    }

//    /** @test */
//    public function it_saves_many_translations_to_database(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->addMany([
//            'title' => 'Моя новая книга',
//            'description' => 'Как хранить переводы для Laravel',
//        ], 'uk');
//
//        self::assertCount(2, Translation::all());
//
//        $this->assertDatabaseHas('translations', [
//            'translatable_id' => $book->id,
//            'translatable_type' => $book->getMorphClass(),
//            'translatable_attribute' => 'title',
//            'value' => 'Моя новая книга',
//            'locale' => 'uk',
//        ]);
//
//        $this->assertDatabaseHas('translations', [
//            'translatable_id' => $book->id,
//            'translatable_type' => $book->getMorphClass(),
//            'translatable_attribute' => 'description',
//            'value' => 'Как хранить переводы для Laravel',
//            'locale' => 'uk',
//        ]);
//    }

//    /** @test */
//    public function it_returns_translation_from_different_locales(): void
//    {
//        $book = BookFactory::new()->create(['title' => 'My original title']);
//
//        $book->translator()->add('title', 'Min ursprungliga titel', 'sv');
//        $book->translator()->add('title', 'Mi titulo original', 'es');
//
//        self::assertEquals('Min ursprungliga titel', $book->translator()->get('title', 'sv'));
//        self::assertEquals('Mi titulo original', $book->translator()->get('title', 'es'));
//        self::assertEquals('My original title', $book->title);
//    }

//    /** @test */
//    public function it_overrides_previous_translations(): void
//    {
//        $book = BookFactory::new()->create();
//
//        $book->translator()->add('title', 'Неправильное название книги', 'uk');
//        $book->translator()->add('title', 'Правильное название книги', 'uk');
//
//        self::assertCount(1, Translation::all());
//        self::assertEquals('Правильное название книги', $book->translator()->get('title', 'uk'));
//    }
//
//    /** @test */
//    public function it_updates_default_value_for_default_locale(): void
//    {
//        $book = BookFactory::new()->create(['title' => 'My book']);
//
//        $book->translator()->add('title', 'My english book', $this->app->getLocale());
//
//        self::assertEquals('My english book', $book->getAttribute('title'));
//        self::assertEmpty(Translation::all());
//    }
//
//    /** @test */
//    public function it_throws_exception_during_translation_non_translatable_attributes(): void
//    {
//        $book = BookFactory::new()->create(['title' => 'My book']);
//
//        try {
//            $book->translator()->add('version', '5', $this->app->getLocale());
//            self::fail('Exception was not thrown for not translatable attribute');
//        } catch (AttributeNotTranslatableException $e) {
//            self::assertCount(0, Translation::all());
//        }
//    }
}
