<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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
            $table->string('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_eager_loads_translations_for_current_locale(): void
    {
        $book = new BookForEagerLoading();
        $book->title = 'Forest';
        $book->save();

        $book->translator()->add('title', 'Ліс', 'uk');
        $book->translator()->add('title', 'Las', 'pl');

        $this->app->setLocale('uk');

        [$book] = BookForEagerLoading::all();

        static::assertTrue($book->relationLoaded('translations'));
        static::assertCount(1, $book->translations);
        static::assertSame('uk', $book->translations[0]->locale);
        static::assertSame('Ліс', $book->translations[0]->value);
    }

    /** @test */
    public function it_performs_only_two_queries_to_retrieve_models_and_translations_with_eager_loading(): void
    {
        $book1 = new BookForEagerLoading();
        $book1->title = 'Atlas of animals';
        $book1->save();

        $book2 = new BookForEagerLoading();
        $book2->title = 'Forest';
        $book2->save();

        $book3 = new BookForEagerLoading();
        $book3->title = 'Encyclopedia of animals';
        $book3->save();

        $book1->translator()->add('title', 'Атлас тварин', 'uk');
        $book2->translator()->add('title', 'Ліс', 'uk');
        $book3->translator()->add('title', 'Енциклопедія тварин', 'uk');

        $this->app->setLocale('uk');

        $this->app[ConnectionInterface::class]->enableQueryLog();

        [$book1, $book2, $book3] = BookForEagerLoading::all();

        static::assertSame('Атлас тварин', $book1->title);
        static::assertSame('Ліс', $book2->title);
        static::assertSame('Енциклопедія тварин', $book3->title);

        static::assertCount(2, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /** @test */
    public function it_does_not_eager_load_translations_in_fallback_locale(): void
    {
        $book = new BookForEagerLoading();
        $book->translator()->set('title', 'Sense Gallery', $this->app->getFallbackLocale());
        $book->save();

        [$book] = BookForEagerLoading::all();

        static::assertFalse($book->relationLoaded('translations'));
    }

    /** @test */
    public function it_allows_disabling_eager_loading_on_query_builder(): void
    {
        $book = new BookForEagerLoading();
        $book->title = 'Atlas of animals';
        $book->save();

        $book->translator()->add('title', 'Атлас тварин', 'uk');

        $this->app->setLocale('uk');

        [$book] = BookForEagerLoading::query()->withoutTranslationsScope()->get();

        static::assertFalse($book->relationLoaded('translations'));
    }

    /** @test */
    public function it_updates_eager_loaded_translations_with_new_set_value(): void
    {
        $book = new BookForEagerLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        [$book] = BookForEagerLoading::all();

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
class BookForEagerLoading extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
