<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class LazyLoadTranslationsTest extends TestCase
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
        $book = new BookForCache();
        $book->title = 'Atlas of animals';
        $book->save();

        $book->translator()->add('title', 'Атлас тварин', 'uk');
        $book->translator()->add('title', 'Atlas zwierząt', 'pl');

        $this->app->setLocale('uk');

        [$book] = BookForCache::all();

        self::assertEquals('Атлас тварин', $book->translator()->get('title', 'uk'));
        self::assertEquals('Atlas zwierząt', $book->translator()->get('title', 'pl'));
    }

    /** @test */
    public function it_can_lazy_load_translations_to_cache_when_no_translations_were_eager_loaded_in_fallback_locale(): void
    {
        $book = new BookForCache();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        [$book] = BookForCache::query()->withoutTranslations()->get();

        DB::connection()->enableQueryLog();

        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'en'));
        self::assertEquals('Атлас тварин', $book->translator()->get('title', 'uk'));

        self::assertCount(1, DB::connection()->getQueryLog());
    }

    /** @test */
    public function it_can_lazy_load_translations_when_no_translations_were_eager_loaded_in_custom_locale(): void
    {
        $book = new BookForCache();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->translator()->set('title', 'Atlas zwierząt', 'pl');
        $book->save();

        $this->app->setLocale('uk');

        [$book] = BookForCache::query()->withoutTranslations()->get();

        DB::connection()->enableQueryLog();

        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'en'));
        self::assertEquals('Атлас тварин', $book->translator()->get('title', 'uk'));
        self::assertEquals('Atlas zwierząt', $book->translator()->get('title', 'pl'));

        self::assertCount(2, DB::connection()->getQueryLog());
    }

    /** @test */
    public function it_lazy_loads_translations_only_once_when_they_are_missing(): void
    {
        $book = new BookForCache();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        [$book] = BookForCache::query()->withoutTranslations()->get();

        DB::connection()->enableQueryLog();

        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'pl'));
        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'pl'));
        self::assertEquals('Atlas of animals', $book->translator()->get('title', 'pl'));

        self::assertCount(1, DB::connection()->getQueryLog());
    }

    /** @test */
    public function it_performs_no_additional_queries_to_retrieve_translation_for_same_attribute_and_locale(): void
    {
        $book = new Book();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        DB::connection()->enableQueryLog();

        $book->translator()->get('title', 'uk');
        $book->translator()->get('title', 'uk');
        $book->translator()->get('title', 'uk');

        self::assertEmpty(DB::connection()->getQueryLog());
    }

    // TODO: it_updates_cached_translation_during_set (sync with pendingTranslation array)

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
class BookForCache extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
