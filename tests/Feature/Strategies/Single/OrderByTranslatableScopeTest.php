<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
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
            $table->timestamps();
        });
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_using_current_locale(): void
    {
        $book1 = new BookWhereTranslatable();
        $book1->title = 'Son of the earth';
        $book1->save();

        $book1->translator()->add('title', 'Син землі', 'uk');

        $book2 = new BookWhereTranslatable();
        $book2->title = 'The last prophet';
        $book2->save();

        $book2->translator()->add('title', 'Останній пророк', 'uk');

        $this->app->setLocale('uk');

        $records = BookWhereTranslatable::query()->orderByTranslatable('title')->get();

        self::assertTrue($records[0]->is($book2));
        self::assertTrue($records[1]->is($book1));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_in_descending_order(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translator()->add('title', 'Первая книга', 'uk');
        $book2->translator()->add('title', 'Вторая книга', 'uk');

        $this->app->setLocale('uk');

        $records = Book::query()->orderByTranslatable('title', 'desc')->get();

        self::assertTrue($records[0]->is($book1));
        self::assertTrue($records[1]->is($book2));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_for_custom_locale(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $book1->translator()->add('title', 'Первая книга', 'uk');
        $book2->translator()->add('title', 'Вторая книга', 'uk');

        $records = Book::query()->orderByTranslatable('title', 'asc', 'uk')->get();

        self::assertTrue($records[0]->is($book2));
        self::assertTrue($records[1]->is($book1));
    }

    /** @test */
    public function it_can_order_by_translatable_attribute_in_default_locale(): void
    {
        $book1 = BookFactory::new()->create(['title' => 'First book']);
        $book2 = BookFactory::new()->create(['title' => 'Second book']);

        $records = Book::query()->orderByTranslatable('title')->get();

        self::assertTrue($records[0]->is($book1));
        self::assertTrue($records[1]->is($book2));
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
 */
class BookOrderByTranslatable extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
