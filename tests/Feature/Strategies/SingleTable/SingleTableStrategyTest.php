<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class SingleTableStrategyTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    /**
     * Set up the database schema.
     */
    private function createSchema(): void
    {
        $this->schema()->create('books', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_stores_translations_using_single_table_strategy(): void
    {
        $book = new Book();
        $book->translator()->set('title', 'Amazing birds', 'en');
        $book->translator()->set('title', 'Дивовижні птахи', 'uk');
        $book->translator()->set('description', 'This book will help you discover all the secrets of birds', 'en');
        $book->translator()->set('description', 'Ця книга допоможе тобі вивідати всі пташині таємниці', 'uk');
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 4);
        $this->assertDatabaseHas('translations', [
            'translatable_type' => $book->getMorphClass(),
            'translatable_id' => $book->getKey(),
            'translatable_attribute' => 'title',
            'locale' => 'en',
            'value' => 'Amazing birds'
        ]);
        $this->assertDatabaseHas('translations', [
            'translatable_type' => $book->getMorphClass(),
            'translatable_id' => $book->getKey(),
            'translatable_attribute' => 'title',
            'locale' => 'uk',
            'value' => 'Дивовижні птахи'
        ]);
        $this->assertDatabaseHas('translations', [
            'translatable_type' => $book->getMorphClass(),
            'translatable_id' => $book->getKey(),
            'translatable_attribute' => 'description',
            'locale' => 'en',
            'value' => 'This book will help you discover all the secrets of birds'
        ]);
        $this->assertDatabaseHas('translations', [
            'translatable_type' => $book->getMorphClass(),
            'translatable_id' => $book->getKey(),
            'translatable_attribute' => 'description',
            'locale' => 'uk',
            'value' => 'Ця книга допоможе тобі вивідати всі пташині таємниці'
        ]);
    }

    /** @test */
    public function it_retrieves_translations_using_single_table_strategy(): void
    {
        $book = new Book();
        $book->translator()->set('title', 'Amazing birds', 'en');
        $book->translator()->set('title', 'Дивовижні птахи', 'uk');
        $book->translator()->set('description', 'This book will help you discover all the secrets of birds', 'en');
        $book->translator()->set('description', 'Ця книга допоможе тобі вивідати всі пташині таємниці', 'uk');
        $book->save();

        self::assertEquals('Amazing birds', $book->translator()->get('title', 'en'));
        self::assertEquals('Дивовижні птахи', $book->translator()->get('title', 'uk'));
        self::assertEquals('This book will help you discover all the secrets of birds', $book->translator()->get('description', 'en'));
        self::assertEquals('Ця книга допоможе тобі вивідати всі пташині таємниці', $book->translator()->get('description', 'uk'));
    }

    /** @test */
    public function it_stores_translations_using_attribute_interceptors_on_single_table_strategy(): void
    {
        $book = new Book();

        $this->app->setLocale('en');
        $book->title = 'Amazing birds';

        $this->app->setLocale('uk');
        $book->title = 'Дивовижні птахи';

        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 2);
        $this->assertDatabaseHas('translations', [
            'translatable_type' => $book->getMorphClass(),
            'translatable_id' => $book->getKey(),
            'translatable_attribute' => 'title',
            'locale' => 'en',
            'value' => 'Amazing birds'
        ]);
        $this->assertDatabaseHas('translations', [
            'translatable_type' => $book->getMorphClass(),
            'translatable_id' => $book->getKey(),
            'translatable_attribute' => 'title',
            'locale' => 'uk',
            'value' => 'Дивовижні птахи'
        ]);
    }

    /** @test */
    public function it_retrieves_translations_using_attribute_interceptors_on_single_table_strategy(): void
    {
        $book = new Book();
        $book->translator()->set('title', 'Amazing birds', 'en');
        $book->translator()->set('title', 'Дивовижні птахи', 'uk');
        $book->save();

        $this->app->setLocale('en');
        self::assertEquals('Amazing birds', $book->title);

        $this->app->setLocale('uk');
        self::assertEquals('Дивовижні птахи', $book->title);
    }

    /** @test */
    public function it_fills_translatable_attributes_with_nulls_when_translations_are_missing(): void
    {
        $book = new Book();
        $book->save();

        $array = $book->toArray();

        self::assertNull($array['title']);
        self::assertNull($array['description']);
    }

    // TODO: create in custom locale
    // TODO: create in fallback locale

//    /** @test */
//    public function it_retrieves_original_value_for_fallback_locale(): void
//    {
//        $book = new Book();
//        $book->title = 'Book about penguins';
//        $book->save();
//
//        $book->translator()->add('title', 'Книга про пінгвінів', 'uk');
//
//        self::assertEquals('Book about penguins', $book->translator()->get('title', 'en'));
//    }
//
//    /** @test */
//    public function it_updates_original_attribute_when_trying_to_translate_attribute_using_fallback_locale(): void
//    {
//        $book = new Book();
//        $book->title = 'Encyclopedia of animals';
//        $book->save();
//
//        $book->translator()->add('title', 'Large encyclopedia of animals', 'en');
//
//        self::assertEquals('Large encyclopedia of animals', $book->title);
//        self::assertEmpty($book->translations);
//    }
//
//    /** @test */
//    public function it_throws_exception_when_trying_to_get_missing_translation(): void
//    {
//        $book = new Book();
//        $book->title = 'Large encyclopedia of animals';
//        $book->save();
//
//        $this->expectException(TranslationMissingException::class);
//
//        $book->translator()->getOrFail('title', 'uk');
//    }
//
//    /** @test */
//    public function it_throws_exception_when_trying_to_get_translation_for_non_translatable_attribute(): void
//    {
//        $book = new Book();
//        $book->title = 'Large encyclopedia of animals';
//        $book->save();
//
//        $this->expectException(AttributeNotTranslatableException::class);
//
//        $book->translator()->get('created_at');
//    }
//
//    /** @test */
//    public function it_throws_exception_when_trying_to_add_translation_for_non_translatable_attribute(): void
//    {
//        $book = new Book();
//        $book->title = 'Large encyclopedia of animals';
//        $book->save();
//
//        try {
//            $book->translator()->add('created_at', now()->setTimezone('Europe/Kiev'), 'uk');
//            self::fail('Exception was not thrown for not translatable attribute');
//        } catch (AttributeNotTranslatableException $e) {
//            $this->assertDatabaseCount('translations', 0);
//        }
//    }
//
//    /** @test */
//    public function it_returns_fallback_value_if_translation_is_missing(): void
//    {
//        $book = new Book();
//        $book->title = 'Atlas of animals';
//        $book->save();
//
//        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'uk'));
//    }
//
//    /** @test */
//    public function it_returns_null_if_translation_is_nullable(): void
//    {
//        $book = new Book();
//        $book->title = 'Atlas of animals';
//        $book->save();
//
//        $book->translator()->add('title', null, 'uk');
//
//        self::assertNull($book->translator()->get('title', 'uk'));
//    }
//
//    // TODO: probably move to strategy specific test.
//
//    /** @test */
//    public function it_saves_translations_to_database(): void
//    {
//        $book = new Book();
//        $book->title = 'Atlas of animals';
//        $book->save();
//
//        $book->translator()->add('title', 'Атлас тварин', 'uk');
//
//        $this->assertDatabaseCount('translations', 1);
//        $this->assertDatabaseHas('translations', [
//            'translatable_id' => $book->getKey(),
//            'translatable_type' => $book->getMorphClass(),
//            'translatable_attribute' => 'title',
//            'value' => 'Атлас тварин',
//            'locale' => 'uk',
//        ]);
//    }
//
//    // TODO: extract this test into 'caching' group
//    /** @test */
//    public function it_retrieves_translation_for_different_locale(): void
//    {
//        $book = new Book();
//        $book->title = 'Wind in willows';
//        $book->save();
//
//        $book->translator()->add('title', 'Вітер у вербах', 'uk');
//        $book->translator()->add('title', 'Wiatr w wierzbach', 'pl');
//
//        self::assertEquals('Вітер у вербах', $book->translator()->get('title', 'uk'));
//        self::assertEquals('Wiatr w wierzbach', $book->translator()->get('title', 'pl'));
//        self::assertEquals('Wind in willows', $book->title);
//    }
//
//    // TODO: extract this test into 'caching' group
//    // TODO: make this test work.
//    /** @test */
//    public function it_overrides_previous_translations(): void
//    {
//        $book = new Book();
//        $book->title = 'The world around us. Wild animals';
//        $book->save();
//
//        $book->translator()->add('title', 'Світ навколо нас', 'uk');
//        self::assertEquals('Світ навколо нас', $book->translator()->get('title', 'uk'));
//
//        // TODO: remove when it will be rewritten using 'loaded strategy structure' on 'retrieved' event.
//        $book = $book->fresh();
//
//        $book->translator()->add('title', 'Світ навколо нас. Дикі тварини', 'uk');
//        self::assertEquals('Світ навколо нас. Дикі тварини', $book->translator()->get('title', 'uk'));
//
//        $this->assertDatabaseCount('translations', 1);
//    }
//
//    /** @test */
//    public function it_does_not_store_pending_translations_twice(): void
//    {
//        $book = new Book();
//        $book->title = 'The world around us';
//        $book->save();
//
//        $this->app->setLocale('uk');
//        $book->translator()->set('title', 'Світ навколо нас', 'uk');
//        $book->save();
//
//        DB::connection()->enableQueryLog();
//
//        $book->save();
//
//        self::assertEmpty(DB::connection()->getQueryLog());
//        $this->assertDatabaseCount('translations', 1);
//    }
//
//    /** @test */
//    public function it_does_not_duplicate_translations(): void
//    {
//        $book = new Book();
//        $book->title = 'The world around us';
//        $book->save();
//
//        $this->app->setLocale('uk');
//
//        $book->translator()->add('title', 'Світ навколо нас', 'uk');
//        $book->translator()->add('title', 'Світ навколо нас', 'uk');
//
//        $this->assertDatabaseCount('translations', 1);
//    }
//
//    /** @test */
//    public function it_does_not_perform_additional_query_for_fallback_locale(): void
//    {
//        $book = new Book();
//        $book->title = 'Book about penguins';
//        $book->save();
//
//        $book->translator()->add('title', 'Книга про пінгвінів', 'uk');
//
//        DB::connection()->enableQueryLog();
//
//        $translation = $book->translator()->get('title', 'en');
//
//        self::assertEmpty(DB::connection()->getQueryLog());
//        self::assertEquals('Book about penguins', $translation);
//    }
//
//    /** @test */
//    public function it_performs_only_one_query_to_retrieve_translation_for_same_attribute_and_locale(): void
//    {
//        $book = new Book();
//        $book->title = 'Book about penguins';
//        $book->save();
//
//        $book->translator()->add('title', 'Книга про пінгвінів', 'uk');
//
//        DB::connection()->enableQueryLog();
//
//        $book->translator()->get('title', 'uk');
//        $book->translator()->get('title', 'uk');
//        $book->translator()->get('title', 'uk');
//
//        self::assertCount(1, DB::connection()->getQueryLog());
//    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string|null title
 * @property string|null description
 */
class Book extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
