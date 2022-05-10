<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\SingleTableExtendedStrategy;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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
        SingleTableExtendedStrategy::disableLazyLoading();
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
    public function it_returns_fallback_translations_when_lazy_loading_is_disabled(): void
    {
        $book = new BookWithDisabledLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->translator()->set('title', 'Atlas zwierząt', 'pl');
        $book->save();

        [$book] = BookWithDisabledLazyLoading::query()->withoutTranslationsScope()->get();

        self::assertFalse($book->relationLoaded('translations'));
        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'en'));
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

        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'uk'));
        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'pl'));
        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'en'));

        self::assertCount(0, $this->app[ConnectionInterface::class]->getQueryLog());
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
        self::assertEquals('Атлас тварин', $book->translator()->get('title', 'uk'));
        self::assertCount(0, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SingleTableExtendedStrategy::enableLazyLoading();
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
