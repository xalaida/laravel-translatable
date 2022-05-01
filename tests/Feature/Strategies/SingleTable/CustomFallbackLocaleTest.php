<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
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
    public function it_stores_model_in_current_application_locale(): void
    {
        $book = new BookWithCustomFallback();
        $book->title = 'Sense Gallery';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseHas('translations', [
            'locale' => $this->app->getFallbackLocale(),
            'value' => 'Sense Gallery',
        ]);
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
        self::assertCount(2, $book->translations);
        self::assertEquals('uk', $book->translations[0]->locale);
        self::assertEquals('pl', $book->translations[1]->locale);
    }

    /** @test */
    public function it_eager_loads_translations_only_for_custom_fallback_locale(): void
    {
        $book = new BookWithCustomFallback();
        $book->translator()->set('title', 'Галерея чуття', 'uk');
        $book->translator()->set('title', 'Sense Galeria', 'pl');
        $book->translator()->set('title', 'Sense Gallery', $this->app->getFallbackLocale());
        $book->save();

        $this->app->setLocale('uk');
        [$book] = BookWithCustomFallback::all();

        self::assertTrue($book->relationLoaded('translations'));
        self::assertCount(1, $book->translations);
        self::assertEquals('uk', $book->translations[0]->locale);
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
class BookWithCustomFallback extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    public function configureTranslator(Translator $translator): void
    {
        $translator->fallbackLocale('uk');
    }
}
