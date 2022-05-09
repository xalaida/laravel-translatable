<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\AdditionalTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Nevadskiy\Translatable\Events\TranslationMissing;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\AdditionalTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;
use Nevadskiy\Translatable\Translator;

class TranslationMissingEventTest extends TestCase
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
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_fires_event_when_translation_is_missing_for_given_attribute_and_locale(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->title = 'Nature clock';
        $book->save();

        Translator::setEventDispatcher(Event::fake(TranslationMissing::class));

        self::assertEquals('Nature clock', $book->translator()->get('title', 'uk'));

        Event::assertDispatched(TranslationMissing::class, static function (TranslationMissing $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === 'uk'
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_fires_event_when_fallback_translation_is_missing(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->save();

        Event::fake(TranslationMissing::class);

        self::assertNull($book->translator()->getFallback('title'));

        Event::assertDispatched(TranslationMissing::class, function (TranslationMissing $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === $this->app->getFallbackLocale()
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_throw_translation_missing_exception_when_trying_to_get_missing_translation(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->title = 'Nature clock';
        $book->save();

        $this->expectException(TranslationMissingException::class);

        $book->translator()->getOrFail('title', 'uk');
    }

    /** @test */
    public function it_throw_translation_missing_exception_when_trying_to_get_fallback_translation(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->save();

        $this->expectException(TranslationMissingException::class);

        $book->translator()->getOrFail('title', $this->app->getFallbackLocale());
    }

    /** @test */
    public function it_does_not_fire_translation_missing_event_when_translation_is_found(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->translator()->set('title', 'Nature clock', 'en');
        $book->translator()->set('title', 'Годинник природи', 'uk');
        $book->save();

        Event::fake(TranslationMissing::class);

        self::assertEquals('Годинник природи', $book->translator()->get('title', 'uk'));

        Event::assertNotDispatched(TranslationMissing::class);
    }

    /** @test */
    public function it_does_not_fire_translation_missing_event_when_translation_is_nullable(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->translator()->set('title', 'Nature clock', 'en');
        $book->translator()->set('title', null, 'uk');
        $book->save();

        Event::fake(TranslationMissing::class);

        self::assertNull($book->translator()->get('title', 'uk'));

        Event::assertNotDispatched(TranslationMissing::class);
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
class BookForTranslationMissingEvent extends Model
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
