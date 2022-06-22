<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\ConnectionInterface;
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
            $table->integer('size')->default(0);
            $table->timestamps();
        });
    }

    /** @test */
    public function it_retrieves_translations_for_current_locale_using_model_getter(): void
    {
        $book = new BookWithGetters();
        $book->translator()->set('title', 'The Bear Book', 'en');
        $book->translator()->set('title', 'Ведмежа книга', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        static::assertSame('Ведмежа книга', $book->title);
    }

    /** @test */
    public function it_uses_fallback_translation_for_getter_if_translation_is_missing(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->save();

        $this->app->setLocale('uk');
        static::assertSame('The Bear Book', $book->title);
    }

    /** @test */
    public function it_returns_original_attribute_for_fallback_locale(): void
    {
        $book = new BookWithGetters();
        $book->translator()->set('title', 'The Bear Book', 'en');
        $book->translator()->set('title', 'Ведмежа книга', 'uk');
        $book->save();

        static::assertSame('The Bear Book', $book->title);
    }

    /** @test */
    public function it_still_retrieves_values_for_non_translatable_attributes(): void
    {
        $book = new BookWithGetters();
        $book->title = 'The Bear Book';
        $book->size = 25;
        $book->save();

        static::assertSame(25, $book->size);
    }

    /** @test */
    public function it_does_not_restore_resolved_values_from_getter_on_save(): void
    {
        $book = new BookWithGetters();
        $book->translator()->set('title', 'The Bear Book', 'en');
        $book->translator()->set('title', 'Ведмежа книга', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        static::assertSame('Ведмежа книга', $book->title);

        $this->app[ConnectionInterface::class]->enableQueryLog();

        $book->save();

        static::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
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
