<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class LazyLoadingTranslationsTest extends TestCase
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
    public function it_can_lazy_load_translation_on_model_after_switching_locale(): void
    {
        $book = new BookForLazyLoading();
        $book->title = 'Atlas of animals';
        $book->save();

        $book->translator()->add('title', 'Атлас тварин', 'uk');
        $book->translator()->add('title', 'Atlas zwierząt', 'pl');

        $this->app->setLocale('uk');

        [$book] = BookForLazyLoading::all();

        static::assertSame('Атлас тварин', $book->translator()->get('title', 'uk'));
        static::assertSame('Atlas zwierząt', $book->translator()->get('title', 'pl'));
    }

    /** @test */
    public function it_can_lazy_load_translations_to_cache_when_no_translations_were_eager_loaded_in_fallback_locale(): void
    {
        $book = new BookForLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        [$book] = BookForLazyLoading::query()->withoutTranslationsScope()->get();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        static::assertSame('Atlas of animals', $book->translator()->get('title', 'en'));
        static::assertSame('Атлас тварин', $book->translator()->get('title', 'uk'));

        static::assertCount(1, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /** @test */
    public function it_can_lazy_load_translations_when_no_translations_were_eager_loaded_in_custom_locale(): void
    {
        $book = new BookForLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->translator()->set('title', 'Atlas zwierząt', 'pl');
        $book->save();

        $this->app->setLocale('uk');

        [$book] = BookForLazyLoading::query()->withoutTranslationsScope()->get();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        static::assertSame('Atlas of animals', $book->translator()->get('title', 'en'));
        static::assertSame('Атлас тварин', $book->translator()->get('title', 'uk'));
        static::assertSame('Atlas zwierząt', $book->translator()->get('title', 'pl'));

        static::assertCount(2, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /** @test */
    public function it_lazy_loads_translations_only_once_when_they_are_missing(): void
    {
        $book = new BookForLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        [$book] = BookForLazyLoading::query()->withoutTranslationsScope()->get();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        static::assertSame('Atlas of animals', $book->translator()->get('title', 'pl'));
        static::assertSame('Atlas of animals', $book->translator()->get('title', 'pl'));
        static::assertSame('Atlas of animals', $book->translator()->get('title', 'pl'));

        static::assertCount(1, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /** @test */
    public function it_performs_no_additional_queries_to_retrieve_translation_for_same_attribute_and_locale(): void
    {
        $book = new BookForLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        $book->translator()->get('title', 'uk');
        $book->translator()->get('title', 'uk');
        $book->translator()->get('title', 'uk');

        static::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
    }

    /** @test */
    public function it_updates_lazy_loaded_translations_with_new_set_value(): void
    {
        $book = new BookForLazyLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        $book = $book->fresh();

        static::assertSame('Атлас тварин', $book->translator()->get('title', 'uk'));

        $book->translator()->set('title', 'Галерея чуття', 'uk');

        static::assertSame('Галерея чуття', $book->translator()->get('title', 'uk'));
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
class BookForLazyLoading extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
