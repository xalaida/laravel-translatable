<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\Translator;

class DisableFallbackTranslationTest extends TestCase
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
    public function it_returns_null_when_fallback_translation_is_disabled(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->save();

        self::assertNull($book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_returns_null_when_translation_is_missing_using_attribute_interceptor(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->save();

        $this->app->setLocale('uk');
        self::assertNull($book->title);
    }

    /** @test */
    public function it_returns_translation_when_it_is_available(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->save();

        self::assertEquals('Галерея чуття', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_returns_fallback_translation_from_fallback_method(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        self::assertEquals('Sense gallery', $book->translator()->getFallback('title'));
    }

    /** @test */
    public function it_creates_model_in_fallback_locale_when_fallback_is_disabled(): void
    {
        $book = new BookWithDisabledFallback();
        $book->title = 'Sense gallery';
        $book->save();

        $this->assertDatabaseHas('books', ['title' => 'Sense gallery']);
        $this->assertDatabaseCount('translations', 0);
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
class BookWithDisabledFallback extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    protected function configureTranslator(Translator $translator): void
    {
        $translator->disableFallback();
    }
}
