<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Nevadskiy\Translatable\Events\TranslationMissing;
use Nevadskiy\Translatable\Exceptions\TranslationMissingException;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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
            $table->text('title');
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
    public function it_throw_translation_missing_exception_when_trying_to_get_missing_translation(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->title = 'Nature clock';
        $book->save();

        $this->app->setLocale('uk');

        $this->expectException(TranslationMissingException::class);

        $book->translator()->getOrFail('title');
    }

    /** @test */
    public function it_does_not_fire_translation_missing_event_when_translation_is_found(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->title = 'Nature clock';
        $book->save();

        $this->app->setLocale('uk');

        $book->translator()->add('title', 'Годинник природи', 'uk');

        Event::fake(TranslationMissing::class);

        self::assertEquals('Годинник природи', $book->translator()->get('title', 'uk'));

        Event::assertNotDispatched(TranslationMissing::class);
    }

    /** @test */
    public function it_does_not_fire_translation_missing_event_when_translation_is_nullable(): void
    {
        $book = new BookForTranslationMissingEvent();
        $book->title = 'Nature clock';
        $book->save();

        $book->translator()->add('title', null, 'uk');

        Event::fake(TranslationMissing::class);

        self::assertNull($book->translator()->get('title', 'uk'));

        Event::assertNotDispatched(TranslationMissing::class);
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
}
