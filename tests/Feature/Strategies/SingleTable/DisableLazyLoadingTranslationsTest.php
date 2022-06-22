<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\SingleTableStrategy;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class DisableLazyLoadingTranslationsTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
        SingleTableStrategy::disableLazyLoading();
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
    public function it_does_not_lazy_load_missing_translation(): void
    {
        $book = new BookWithDisabledLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->translator()->set('title', 'Atlas zwierząt', 'pl');
        $book->save();

        [$book] = BookWithDisabledLazyLoading::query()->withoutTranslationsScope()->get();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        static::assertNull($book->translator()->get('title', 'uk'));
        static::assertNull($book->translator()->get('title', 'pl'));

        static::assertCount(0, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /** @test */
    public function it_returns_eager_loaded_translations(): void
    {
        $book = new BookWithDisabledLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        [$book] = BookWithDisabledLazyLoading::query()
            ->withoutTranslationsScope()
            ->with(['translations' => function (Relation $query) {
                $query->forLocale('uk');
            }])
            ->get();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        static::assertSame('Атлас тварин', $book->translator()->get('title', 'uk'));
        static::assertNull($book->translator()->get('title', 'en'));

        static::assertCount(0, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SingleTableStrategy::enableLazyLoading();
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string title
 */
class BookWithDisabledLazyLoading extends Model
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
