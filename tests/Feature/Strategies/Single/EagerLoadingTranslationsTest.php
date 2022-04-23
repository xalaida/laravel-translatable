<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

// TODO: add possibility to disable eager loading in model.
// TODO: structure loaded translation in the strategy as this: ['en' => ['title' => null], 'uk' => ['title' => 'Книга']]

class EagerLoadingTranslationsTest extends TestCase
{
    /**
     * Set up the test environment.
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
            $table->text('description')->nullable();
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

        self::assertTrue($book->relationLoaded('translations'));
        self::assertCount(1, $book->translations);
        self::assertEquals('uk', $book->translations[0]->locale);
        self::assertEquals('Ліс', $book->translations[0]->value);
    }

    /** @test */
    public function it_performs_only_two_queries_to_retrieve_models_and_translations_with_eager_loading(): void
    {
        // TODO: feature ModelFactory::new()->for(BookForEagerLoading::class)->createMany([ [1], [2], [3] ])
        // TODO: also configure attributes with faker ->attributes(['title' => fn () => $this->faker->title])

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

        DB::connection()->enableQueryLog();

        [$book1, $book2, $book3] = BookForEagerLoading::all();

        self::assertEquals('Атлас тварин', $book1->title);
        self::assertEquals('Ліс', $book2->title);
        self::assertEquals('Енциклопедія тварин', $book3->title);

        self::assertCount(2, DB::connection()->getQueryLog());
    }

    /** @test */
    public function it_allows_disabling_eager_loading_on_query_builder(): void
    {
        $book = new BookForEagerLoading();
        $book->title = 'Atlas of animals';
        $book->save();

        $book->translator()->add('title', 'Атлас тварин', 'uk');

        [$book] = BookForEagerLoading::query()->withoutTranslations()->get();

        self::assertFalse($book->relationLoaded('translations'));
    }

    /**
     * Tear down the test.
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string title
 * @property string|null description
 */
class BookForEagerLoading extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];
}
