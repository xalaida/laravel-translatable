<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\Translator;

class CustomFallbackLocaleTest extends TestCase
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
    public function it_retrieves_fallback_translation_using_custom_locale(): void
    {
        $book = new BookWithCustomFallback();
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->translator()->set('title', 'Sense Gallery', $this->app->getFallbackLocale());
        $book->save();

        $this->app->setLocale('uk');
        self::assertEquals('Галерея чуття', $book->translator()->get('title', 'pl'));
    }

    /** @test */
    public function it_retrieves_fallback_translation_using_fallback_method_for_custom_locale(): void
    {
        $book = new BookWithCustomFallback();
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->translator()->set('title', 'Sense Gallery', $this->app->getFallbackLocale());
        $book->save();

        self::assertEquals('Галерея чуття', $book->translator()->getFallback('title'));
    }

    /** @test */
    public function it_stores_model_in_fallback_locale_correctly(): void
    {
        $this->app->setLocale('uk');

        $book = new BookWithCustomFallback();
        $book->title = 'Галерея чуття';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', ['title' => 'Галерея чуття']);
        $this->assertDatabaseCount('book_translations', 0);
    }

    /** @test */
    public function it_eager_loads_translations_in_current_locale_and_custom_fallback_locale(): void
    {
        $book = new BookWithCustomFallback();
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->translator()->set('title', 'Sense Galeria', 'pl');
        $book->translator()->set('title', 'Sense Gallery', $this->app->getFallbackLocale());
        $book->save();

        $this->app->setLocale('pl');
        [$book] = BookWithCustomFallback::all();

        self::assertTrue($book->relationLoaded('translations'));
        self::assertCount(1, $book->translations);
        self::assertEquals('pl', $book->translations[0]->locale);
    }

    /** @test */
    public function it_does_not_eager_load_translations_in_fallback_translator_locale(): void
    {
        $book = new BookWithCustomFallback();
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->translator()->set('title', 'Sense Galeria', 'pl');
        $book->translator()->set('title', 'Sense Gallery', $this->app->getFallbackLocale());
        $book->save();

        $this->app->setLocale('uk');
        [$book] = BookWithCustomFallback::all();

        self::assertFalse($book->relationLoaded('translations'));
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
class BookWithCustomFallback extends Model
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

    public function configureTranslator(Translator $translator): void
    {
        $translator->fallbackLocale('uk');
    }
}
