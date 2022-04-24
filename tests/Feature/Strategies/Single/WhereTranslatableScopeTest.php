<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class WhereTranslatableScopeTest extends TestCase
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
    public function it_queries_records_by_translatable_attribute(): void
    {
        $book = new BookWhereTranslatable();
        $book->title = 'The last prophet';
        $book->save();

        $anotherBook = new BookWhereTranslatable();
        $anotherBook->title = 'Forest';
        $anotherBook->save();

        $book->translator()->add('title', 'Останній пророк', 'uk');

        $records = BookWhereTranslatable::query()->whereTranslatable('title', 'Останній пророк')->get();

        self::assertCount(1, $records);
        self::assertTrue($records[0]->is($book));
    }

    /** @test */
    public function it_queries_records_by_translatable_attribute_with_original_value(): void
    {
        $book = new BookWhereTranslatable();
        $book->title = 'The last prophet';
        $book->save();

        $anotherBook = new BookWhereTranslatable();
        $anotherBook->title = 'Forest';
        $anotherBook->save();

        $book->translator()->add('title', 'Останній пророк', 'uk');

        $records = BookWhereTranslatable::query()->whereTranslatable('title', 'The last prophet')->get();

        self::assertCount(1, $records);
        self::assertTrue($records[0]->is($book));
    }

    /** @test */
    public function it_queries_records_by_translatable_attribute_and_locale(): void
    {
        $book = new BookWhereTranslatable();
        $book->title = 'The last prophet';
        $book->save();

        $book->translator()->add('title', 'Останній пророк', 'uk');

        $anotherBook = new BookWhereTranslatable();
        $anotherBook->title = 'Forest';
        $anotherBook->save();

        $book->translator()->add('title', 'Останній пророк', 'pl');

        $records = BookWhereTranslatable::query()->whereTranslatable('title', 'Останній пророк', 'uk')->get();

        self::assertCount(1, $records);
        self::assertTrue($records[0]->is($book));
    }

    /** @test */
    public function it_returns_no_results_when_querying_records_by_custom_locale(): void
    {
        $book = new BookWhereTranslatable();
        $book->title = 'The last prophet';
        $book->save();

        $book->translator()->add('title', 'Останній пророк', 'uk');

        $records = BookWhereTranslatable::query()->whereTranslatable('title', 'Останній пророк', 'pl')->get();

        self::assertEmpty($records);
    }

    /** @test */
    public function it_returns_no_results_when_querying_records_by_fallback_locale(): void
    {
        $book = new BookWhereTranslatable();
        $book->title = 'The last prophet';
        $book->save();

        $book->translator()->add('title', 'Останній пророк', 'uk');

        $records = BookWhereTranslatable::query()->whereTranslatable('title', 'Останній пророк', 'en')->get();

        self::assertEmpty($records);
    }

    /** @test */
    public function it_queries_records_by_translatable_attribute_within_all_locales(): void
    {
        $book1 = new BookWhereTranslatable();
        $book1->title = 'The last prophet';
        $book1->save();

        $book1->translator()->add('title', 'Останній пророк', 'uk');

        $book2 = new BookWhereTranslatable();
        $book2->title = 'The last prophet';
        $book2->save();

        $book2->translator()->add('title', 'Останній пророк', 'pl');

        $book3 = new BookWhereTranslatable();
        $book3->title = 'Day of Wrath';
        $book3->save();

        $books = BookWhereTranslatable::query()->whereTranslatable('title', 'Останній пророк')->get();

        self::assertCount(2, $books);
        self::assertTrue($books[0]->is($book1));
        self::assertTrue($books[1]->is($book2));
    }

    /** @test */
    public function it_queries_records_by_translatable_attribute_using_like_operator(): void
    {
        $book1 = new BookWhereTranslatable();
        $book1->title = 'The last prophet';
        $book1->save();

        $book1->translator()->add('title', 'Останній пророк', 'uk');

        $book2 = new BookWhereTranslatable();
        $book2->title = 'The first prophet';
        $book2->save();

        $book2->translator()->add('title', 'Перший пророк', 'uk');

        $book3 = new BookWhereTranslatable();
        $book3->title = 'Day of Wrath';
        $book3->save();

        $records = BookWhereTranslatable::whereTranslatable('title', '%пророк', null, 'LIKE')->get();

        self::assertCount(2, $records);
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
class BookWhereTranslatable extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
