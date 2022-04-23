<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Nevadskiy\Translatable\Events\TranslationNotFound;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

// TODO: check if translation is not fired
// TODO: check when using fallback
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
    public function it_fires_an_event_when_translation_is_not_found_for_the_given_attribute_and_locale(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        $this->app->setLocale('ru');

        Event::fake(TranslationNotFound::class);

        self::assertEquals('My original book', $book->title);

        Event::assertDispatched(TranslationNotFound::class, static function (TranslationNotFound $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === 'ru'
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_fires_an_event_when_translation_is_not_found_using_method(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        Event::fake(TranslationNotFound::class);

        self::assertNull($book->translator()->get('title', 'ru'));

        Event::assertDispatched(TranslationNotFound::class, static function (TranslationNotFound $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === 'ru'
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_does_not_fire_translation_not_found_event_when_translation_is_available(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        $book->translator()->add('title', 'Моя оригинальная книга', 'ru');

        Event::fake(TranslationNotFound::class);

        self::assertEquals('Моя оригинальная книга', $book->translator()->get('title', 'ru'));

        Event::assertNotDispatched(TranslationNotFound::class);
    }
}
