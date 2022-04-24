<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class FallbackTranslationTest extends TestCase
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
    public function it_retrieves_fallback_translation(): void
    {
        $book = new BookWithFallback();
        $book->title = 'Sense gallery';
        $book->save();

        $this->app->setLocale('uk');
        $book->translator()->add('title', 'Галерея чуття', 'uk');

        self::assertEquals('Sense gallery', $book->translator()->getFallback('title'));
    }

    /** @test */
    public function it_retrieves_fallback_translation_with_accessor_applied(): void
    {
        $book = new BookWithFallback();
        $book->title = 'sense gallery';
        $book->save();

        $this->app->setLocale('uk');
        $book->translator()->add('title', 'Галерея чуття', 'uk');

        self::assertEquals('Sense gallery', $book->translator()->getFallback('title'));
    }

    /** @test */
    public function it_retrieves_raw_fallback_translation(): void
    {
        $book = new BookWithFallback();
        $book->title = 'sense gallery';
        $book->save();

        $this->app->setLocale('uk');
        $book->translator()->add('title', 'Галерея чуття', 'uk');

        self::assertEquals('sense gallery', $book->translator()->getRawFallback('title'));
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
 */
class BookWithFallback extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }
}
