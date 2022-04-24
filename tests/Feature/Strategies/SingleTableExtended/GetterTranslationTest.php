<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class GetterTranslationTest extends TestCase
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
            $table->string('title');
            $table->integer('size')->default(0);
            $table->timestamps();
        });
    }

    /** @test */
    public function it_retrieves_translations_for_current_locale_using_model_getter(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->save();

        $book->translator()->add('title', 'Ведмежа книга', 'uk');

        $this->app->setLocale('uk');

        self::assertEquals('Ведмежа книга', $book->title);
    }

    /** @test */
    public function it_uses_original_value_for_getter_if_translation_is_missing(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->save();

        $this->app->setLocale('uk');

        self::assertEquals('The Bear Book', $book->title);
    }

    /** @test */
    public function it_can_return_original_attribute(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->save();

        $book->translator()->add('title', 'Ведмежа книга', 'uk');

        $this->app->setLocale('uk');

        self::assertEquals('The Bear Book', $book->getOriginalAttribute('title'));
    }

    /** @test */
    public function it_returns_original_attribute_for_fallback_locale(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->save();

        $book->translator()->add('title', 'Ведмежа книга', 'uk');

        self::assertEquals('The Bear Book', $book->title);
    }

    /** @test */
    public function it_still_retrieves_values_for_non_translatable_attributes(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->size = 25;
        $book->save();

        self::assertEquals(25, $book->size);
    }

    /** @test */
    public function it_does_not_store_retrieved_values_again(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->save();

        $book->translator()->add('title', 'Ведмежа книга', 'uk');

        $this->app->setLocale('uk');

        self::assertEquals('Ведмежа книга', $book->title);

        DB::connection()->enableQueryLog();

        $book->save();

        self::assertEmpty(DB::connection()->getQueryLog());
    }

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
 * @property string title
 * @property int size
 */
class BookWithGetters extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
