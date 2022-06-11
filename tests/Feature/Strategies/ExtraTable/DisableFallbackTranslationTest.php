<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
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
    public function it_returns_null_when_fallback_translation_is_disabled(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->save();

        static::assertNull($book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_returns_null_when_translation_is_missing_using_attribute_interceptor(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->save();

        $this->app->setLocale('uk');
        static::assertNull($book->title);
    }

    /** @test */
    public function it_returns_translation_when_it_is_available(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->save();

        static::assertSame('Галерея чуття', $book->translator()->get('title', 'uk'));
    }

    /** @test */
    public function it_returns_fallback_translation_from_fallback_method(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Sense gallery', $this->app->getFallbackLocale());
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        static::assertSame('Sense gallery', $book->translator()->getFallback('title'));
    }

    /** @test */
    public function it_creates_model_in_fallback_locale_when_fallback_is_disabled(): void
    {
        $book = new BookWithDisabledFallback();
        $book->title = 'Sense gallery';
        $book->save();

        $this->assertDatabaseHas('book_translations', [
            'title' => 'Sense gallery',
            'locale' => $this->app->getFallbackLocale(),
        ]);
    }

    /** @test */
    public function it_does_not_eager_load_disabled_fallback_translations(): void
    {
        $book = new BookWithDisabledFallback();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        $this->app->setLocale('uk');
        [$book] = BookWithDisabledFallback::all();

        static::assertTrue($book->relationLoaded('translations'));
        static::assertCount(1, $book->translations);
        static::assertSame('uk', $book->translations[0]->locale);
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
 */
class BookWithDisabledFallback extends Model
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

    protected function configureTranslator(Translator $translator): void
    {
        $translator->disableFallback();
    }
}
