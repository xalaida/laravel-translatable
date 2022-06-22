<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;
use function route;

/**
 * TODO: add support for resolveChildRouteBinding() method.
 */
class RouteModelBindingTest extends TestCase
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

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title')->nullable();
            $table->string('slug');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_resolves_model_from_route_binding_by_translatable_attribute(): void
    {
        Route::middleware(SubstituteBindings::class)->get('/books/{book}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->translator()->set('slug', 'swan-flock', 'en');
        $book->translator()->set('slug', 'лебедина-зграя', 'uk');
        $book->save();

        $anotherBook = new BookWithRouteBinding();
        $anotherBook->translator()->set('slug', 'two-hetmans', 'en');
        $anotherBook->translator()->set('slug', 'два-гетьмани', 'uk');
        $anotherBook->save();

        $response = $this->get('/books/лебедина-зграя');

        $response->assertOk();
        static::assertSame($book->getKey(), (int) $response->content());
    }

    /** @test */
    public function it_resolves_model_from_route_binding_using_custom_attribute(): void
    {
        Route::middleware(SubstituteBindings::class)->get('/books/{book:title}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->translator()->set('title', 'Лебедина Зграя', 'uk');
        $book->translator()->set('slug', 'лебедина-зграя', 'uk');
        $book->save();

        $response = $this->get('/books/Лебедина Зграя');

        $response->assertOk();
        static::assertSame($book->getKey(), (int) $response->content());
    }

    /** @test */
    public function it_resolves_model_from_route_binding_using_non_translatable_attribute(): void
    {
        Route::middleware(SubstituteBindings::class)->get('/books/{book:id}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->slug = 'swan-flock';
        $book->save();

        $response = $this->get("/books/{$book->getKey()}");

        $response->assertOk();
        static::assertSame($book->getKey(), (int) $response->content());
    }

    /** @test */
    public function it_resolves_model_from_route_binding_using_fallback_translation(): void
    {
        Route::middleware(SubstituteBindings::class)->get('/books/{book}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->translator()->set('slug', 'swan-flock', 'en');
        $book->translator()->set('slug', 'лебедина-зграя', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        $response = $this->get('/books/swan-flock');

        $response->assertOk();
        static::assertSame($book->getKey(), (int) $response->content());
    }

    /** @test */
    public function it_returns_404_when_model_is_not_found(): void
    {
        Route::middleware(SubstituteBindings::class)->get('/books/{book}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->translator()->set('slug', 'swan-flock', 'en');
        $book->translator()->set('slug', 'лебедина-зграя', 'uk');
        $book->save();

        $this->get('/books/two-hetmans')
            ->assertNotFound();
    }

    /** @test */
    public function it_generates_url_from_named_route_using_translatable_attribute(): void
    {
        Route::get('/books/{book}')->name('books.show');

        $book = new BookWithRouteBinding();
        $book->translator()->set('slug', 'swan-flock', 'en');
        $book->translator()->set('slug', 'лебедина-зграя', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        static::assertSame('/books/'.rawurlencode('лебедина-зграя'), route('books.show', $book, false));
    }

    /** @test */
    public function it_still_generates_url_from_named_route_in_fallback_locale(): void
    {
        Route::get('/books/{book}')->name('books.show');

        $book = new BookWithRouteBinding();
        $book->translator()->set('slug', 'swan-flock', $this->app->getFallbackLocale());
        $book->translator()->set('slug', 'лебедина-зграя', 'uk');
        $book->save();

        static::assertSame('/books/swan-flock', route('books.show', $book, false));
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
 * @property string|null title
 * @property string slug
 */
class BookWithRouteBinding extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'slug',
    ];

    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
