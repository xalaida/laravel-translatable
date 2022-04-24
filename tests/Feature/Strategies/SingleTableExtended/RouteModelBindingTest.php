<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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
            $table->foreignId('author_id')->nullable();
            $table->string('title')->nullable();
            $table->string('slug');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_resolves_model_from_route_binding_by_translatable_attribute(): void
    {
        Route::middleware('bindings')->get('/books/{book}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->slug = 'swan-flock';
        $book->save();

        $book->translator()->add('slug', 'лебедина-зграя', 'uk');

        $anotherBook = new BookWithRouteBinding();
        $anotherBook->slug = 'two-hetmans';
        $anotherBook->save();

        $anotherBook->translator()->add('slug', 'два-гетьмани', 'uk');

        $response = $this->get('/books/лебедина-зграя');

        $response->assertOk();
        self::assertEquals($book->getKey(), $response->content());
    }

    /** @test */
    public function it_resolves_model_from_route_binding_using_custom_attribute(): void
    {
        Route::middleware('bindings')->get('/books/{book:title}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->title = 'Swan Flock';
        $book->slug = 'swan-flock';
        $book->save();

        $book->translator()->add('title', 'Лебедина Зграя', 'uk');

        $response = $this->get('/books/Лебедина Зграя');

        $response->assertOk();
        self::assertEquals($book->getKey(), $response->content());
    }

    /** @test */
    public function it_resolves_model_from_route_binding_using_non_translatable_attribute(): void
    {
        Route::middleware('bindings')->get('/books/{book:id}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->slug = 'swan-flock';
        $book->save();

        $response = $this->get("/books/{$book->getKey()}");

        $response->assertOk();
        self::assertEquals($book->getKey(), $response->content());
    }

    /** @test */
    public function it_resolves_model_from_route_binding_using_fallback_translation(): void
    {
        Route::middleware('bindings')->get('/books/{book}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->slug = 'swan-flock';
        $book->save();

        $book->translator()->add('slug', 'лебедина-зграя', 'uk');

        $this->app->setLocale('uk');

        $response = $this->get('/books/swan-flock');

        $response->assertOk();
        self::assertEquals($book->getKey(), $response->content());
    }

    /** @test */
    public function it_returns_404_when_model_is_not_found(): void
    {
        Route::middleware('bindings')->get('/books/{book}', function (BookWithRouteBinding $book) {
            return $book->getKey();
        });

        $book = new BookWithRouteBinding();
        $book->slug = 'swan-flock';
        $book->save();

        $book->translator()->add('slug', 'лебедина-зграя', 'uk');

        $response = $this->get('/books/two-hetmans');

        $response->assertNotFound();
    }

    /** @test */
    public function it_generates_url_from_named_route_using_translatable_attribute(): void
    {
        Route::get('/books/{book}')->name('books.show');

        $book = new BookWithRouteBinding();
        $book->slug = 'swan-flock';
        $book->save();

        $book->translator()->add('slug', 'лебедина-зграя', 'uk');

        $this->app->setLocale('uk');

        $url = route('books.show', $book, false);

        self::assertEquals('/books/'.rawurlencode('лебедина-зграя'), $url);
    }

    /** @test */
    public function it_still_generates_url_from_named_route_in_fallback_locale(): void
    {
        Route::get('/books/{book}')->name('books.show');

        $book = new BookWithRouteBinding();
        $book->slug = 'swan-flock';
        $book->save();

        $book->translator()->add('slug', 'лебедина-зграя', 'uk');

        self::assertEquals('/books/swan-flock', route('books.show', $book, false));
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
