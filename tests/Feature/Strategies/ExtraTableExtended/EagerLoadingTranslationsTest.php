<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
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

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_does_not_eager_load_translations_for_fallback_locale(): void
    {
        $book = new BookForEagerLoading();
        $book->translator()->set('title', 'Forest', 'en');
        $book->save();

        [$book] = BookForEagerLoading::all();

        static::assertFalse($book->relationLoaded('translations'));
    }

    /** @test */
    public function it_eager_loads_translations_for_custom_current_locale_when_custom_locale_is_set(): void
    {
        $book = new BookForEagerLoading();
        $book->translator()->set('title', 'Forest', 'en');
        $book->translator()->set('title', 'Ліс', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        [$book] = BookForEagerLoading::all();

        static::assertTrue($book->relationLoaded('translations'));
        static::assertCount(1, $book->translations);
        static::assertSame('uk', $book->translations[0]->locale);
    }

    /** @test */
    public function it_performs_only_two_queries_to_retrieve_models_and_translations_with_eager_loading(): void
    {
        $book1 = new BookForEagerLoading();
        $book1->translator()->set('title', 'Atlas of animals', 'en');
        $book1->translator()->set('title', 'Атлас тварин', 'uk');
        $book1->save();

        $book2 = new BookForEagerLoading();
        $book2->translator()->set('title', 'Forest', 'en');
        $book2->translator()->set('title', 'Ліс', 'uk');
        $book2->save();

        $book3 = new BookForEagerLoading();
        $book3->translator()->set('title', 'Encyclopedia of animals', 'en');
        $book3->translator()->set('title', 'Енциклопедія тварин', 'uk');
        $book3->save();

        $this->app->setLocale('uk');

        $this->app[ConnectionInterface::class]->enableQueryLog();

        [$book1, $book2, $book3] = BookForEagerLoading::all();

        static::assertSame('Атлас тварин', $book1->title);
        static::assertSame('Ліс', $book2->title);
        static::assertSame('Енциклопедія тварин', $book3->title);

        static::assertCount(2, $this->app[ConnectionInterface::class]->getQueryLog());
    }

    /** @test */
    public function it_allows_disabling_eager_loading_on_query_builder(): void
    {
        $book = new BookForEagerLoading();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

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
        $this->schema()->drop('book_translations');
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

    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }
}
