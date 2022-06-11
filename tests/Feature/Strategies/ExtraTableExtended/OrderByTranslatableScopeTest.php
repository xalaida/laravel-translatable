<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
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
            $table->string('locale')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_using_current_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->translator()->set('title', 'Son of the earth', 'en');
        $book1->translator()->set('title', 'Син землі', 'uk');
        $book1->save();

        $book2 = new BookOrderByTranslatable();
        $book2->translator()->add('title', 'The last prophet', 'en');
        $book2->translator()->add('title', 'Останній пророк', 'uk');
        $book2->save();

        $this->app->setLocale('uk');

        $records = BookOrderByTranslatable::query()
            ->orderByTranslatable('title')
            ->get();

        static::assertTrue($records[0]->is($book2));
        static::assertTrue($records[1]->is($book1));
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_in_descending_order_using_current_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->translator()->set('title', 'Son of the earth', 'en');
        $book1->translator()->set('title', 'Син землі', 'uk');
        $book1->save();

        $book2 = new BookOrderByTranslatable();
        $book2->translator()->add('title', 'The last prophet', 'en');
        $book2->translator()->add('title', 'Останній пророк', 'uk');
        $book2->save();

        $book2->translator()->add('title', 'Останній пророк', 'uk');

        $this->app->setLocale('uk');

        $records = BookOrderByTranslatable::query()
            ->orderByTranslatable('title', 'desc')
            ->get();

        static::assertTrue($records[0]->is($book1));
        static::assertTrue($records[1]->is($book2));
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_using_custom_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->translator()->set('title', 'Son of the earth', 'en');
        $book1->translator()->set('title', 'Син землі', 'uk');
        $book1->save();

        $book2 = new BookOrderByTranslatable();
        $book2->translator()->add('title', 'The last prophet', 'en');
        $book2->translator()->add('title', 'Останній пророк', 'uk');
        $book2->save();

        $book2->translator()->add('title', 'Останній пророк', 'uk');

        $records = BookOrderByTranslatable::query()
            ->orderByTranslatable('title', 'asc', 'uk')
            ->get();

        static::assertTrue($records[0]->is($book2));
        static::assertTrue($records[1]->is($book1));
    }

    /** @test */
    public function it_orders_records_by_translatable_attribute_using_fallback_locale(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->title = 'The last prophet';
        $book1->save();

        $book2 = new BookOrderByTranslatable();
        $book2->title = 'Son of the earth';
        $book2->save();

        $records = BookOrderByTranslatable::query()
            ->orderByTranslatable('title', 'asc', 'en')
            ->get();

        static::assertTrue($records[0]->is($book2));
        static::assertTrue($records[1]->is($book1));
    }

    /** @test */
    public function it_does_not_override_attributes_with_translatable_model_when_ordering_by_translatable_attribute(): void
    {
        $book = new BookOrderByTranslatable();
        $book->translator()->set('title', 'Son of the earth', 'en');
        $book->translator()->set('title', 'Син землі', 'uk');
        $book->locale = 'Fallback';
        $book->save();

        $this->app->setLocale('uk');

        $records = BookOrderByTranslatable::query()
            ->orderByTranslatable('title', 'desc')
            ->get();

        static::assertTrue($records[0]->is($book));
        static::assertSame('Fallback', $records[0]->locale);
    }

    /** @test */
    public function it_can_select_only_specified_attributes_when_ordering_by_translatable_column(): void
    {
        $book1 = new BookOrderByTranslatable();
        $book1->translator()->set('title', 'Son of the earth', 'en');
        $book1->translator()->set('title', 'Син землі', 'uk');
        $book1->save();

        $book2 = new BookOrderByTranslatable();
        $book2->translator()->set('title', 'The last prophet', 'en');
        $book2->translator()->set('title', 'Останній пророк', 'uk');
        $book2->save();

        $this->app->setLocale('uk');

        $records = BookOrderByTranslatable::query()
            ->select(['books.id'])
            ->orderByTranslatable('title')
            ->get();

        static::assertTrue($records[0]->is($book2));
        static::assertTrue($records[1]->is($book1));
        static::assertFalse(isset($records[0]->locale));
        static::assertFalse(isset($records[0]->created_at));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('book_translations');
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string title
 * @property string|null locale
 */
class BookOrderByTranslatable extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }
}
