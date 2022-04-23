<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Nevadskiy\Translatable\Events\TranslationNotFound;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class FireTranslationNotFoundEventTest extends TestCase
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
            $table->text('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_fires_event_when_translation_is_not_found_for_the_given_attribute_and_locale(): void
    {
        $book = new BookForTranslationNotFoundEvent();
        $book->title = 'Nature clock';
        $book->save();

        $this->app->setLocale('uk');

        Event::fake(TranslationNotFound::class);

        self::assertNull($book->translator()->get('title'));

        Event::assertDispatched(TranslationNotFound::class, static function (TranslationNotFound $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === 'uk'
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_fires_translation_not_found_event_using_fallback_method(): void
    {
        $book = new BookForTranslationNotFoundEvent();
        $book->title = 'Nature clock';
        $book->save();

        $this->app->setLocale('uk');

        Event::fake(TranslationNotFound::class);

        self::assertEquals('Nature clock', $book->translator()->getOrFallback('title'));

        Event::assertDispatched(TranslationNotFound::class, static function (TranslationNotFound $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === 'uk'
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_does_not_fire_translation_not_found_event_when_translation_is_found(): void
    {
        $book = new BookForTranslationNotFoundEvent();
        $book->title = 'Nature clock';
        $book->save();

        $this->app->setLocale('uk');

        $book->translator()->add('title', 'Годинник природи', 'uk');

        Event::fake(TranslationNotFound::class);

        self::assertEquals('Годинник природи', $book->translator()->get('title', 'uk'));

        Event::assertNotDispatched(TranslationNotFound::class);
    }
}

/**
 * @property string title
 */
class BookForTranslationNotFoundEvent extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
