<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class EagerLoadingTranslationsTest extends TestCase
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
    public function it_loads_translations_using_single_database_query(): void
    {
        $book1 = new BookWithEagerLoading();
        $book1->translator()->set('title', 'Amazing birds', 'en');
        $book1->translator()->set('title', 'Дивовижні птахи', 'uk');
        $book1->save();

        $book2 = new BookWithEagerLoading();
        $book2->translator()->set('title', 'Doctors in the animal world', 'en');
        $book2->translator()->set('title', 'Лікарі у світі тварин', 'uk');
        $book2->save();

        $this->app->setLocale('uk');

        DB::connection()->enableQueryLog();

        $records = BookWithEagerLoading::query()->get();

        self::assertCount(1, DB::connection()->getQueryLog());
        self::assertTrue($records[0]->is($book1));
        self::assertEquals('Дивовижні птахи', $records[0]->title);
        self::assertTrue($records[0]->created_at->eq($book1->created_at));
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
 * @property string|null title
 * @property string|null description
 */
class BookWithEagerLoading extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
