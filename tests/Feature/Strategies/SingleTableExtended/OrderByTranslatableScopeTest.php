<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class OrderByTranslatableScopeTest extends TestCase
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
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_using_current_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->title = 'Son of the earth';
        $book1->save();

        $book1->translator()->add('title', 'Син землі', 'uk');

        $book2 = new BookOrderByTranslatable();
        $book2->title = 'The last prophet';
        $book2->save();

        $book2->translator()->add('title', 'Останній пророк', 'uk');

        $this->app->setLocale('uk');

        $records = BookOrderByTranslatable::query()->orderByTranslatable('title')->get();

        self::assertTrue($records[0]->is($book2));
        self::assertTrue($records[1]->is($book1));
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_in_descending_order_using_current_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->title = 'Son of the earth';
        $book1->save();

        $book1->translator()->add('title', 'Син землі', 'uk');

        $book2 = new BookOrderByTranslatable();
        $book2->title = 'The last prophet';
        $book2->save();

        $book2->translator()->add('title', 'Останній пророк', 'uk');

        $this->app->setLocale('uk');

        $records = BookOrderByTranslatable::query()->orderByTranslatable('title', 'desc')->get();

        self::assertTrue($records[0]->is($book1));
        self::assertTrue($records[1]->is($book2));
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_using_custom_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->title = 'Son of the earth';
        $book1->save();

        $book1->translator()->add('title', 'Син землі', 'uk');

        $book2 = new BookOrderByTranslatable();
        $book2->title = 'The last prophet';
        $book2->save();

        $book2->translator()->add('title', 'Останній пророк', 'uk');

        $records = BookOrderByTranslatable::query()->orderByTranslatable('title', 'asc', 'uk')->get();

        self::assertTrue($records[0]->is($book2));
        self::assertTrue($records[1]->is($book1));
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_using_fallback_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->title = 'Son of the earth';
        $book1->save();

        $book2 = new BookOrderByTranslatable();
        $book2->title = 'The last prophet';
        $book2->save();

        $records = BookOrderByTranslatable::query()->orderByTranslatable('title', 'asc', 'en')->get();

        self::assertTrue($records[0]->is($book1));
        self::assertTrue($records[1]->is($book2));
    }

    /** @test */
    public function it_does_not_override_attributes_with_translatable_model_when_ordering_by_translatable_attribute(): void
    {
        $book = new BookOrderByTranslatable();
        $book->title = 'Son of the earth';
        $book->value = 'Original value';
        $book->save();

        $book->translator()->add('title', 'Син землі', 'uk');

        $this->app->setLocale('uk');

        $records = BookOrderByTranslatable::query()->orderByTranslatable('title', 'desc')->get();

        self::assertTrue($records[0]->is($book));
        self::assertEquals('Original value', $records[0]->value);
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
 * @property string|null value
 */
class BookOrderByTranslatable extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
