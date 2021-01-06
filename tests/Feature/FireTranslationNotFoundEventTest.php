<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Nevadskiy\Translatable\Events\TranslationNotFoundEvent;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class FireTranslationNotFoundEventTest extends TestCase
{
    /** @test */
    public function it_fires_an_event_when_translation_is_not_found_using_attribute(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        $this->app->setLocale('ru');

        Event::fake(TranslationNotFoundEvent::class);

        self::assertEquals('My original book', $book->title);

        Event::assertDispatched(TranslationNotFoundEvent::class, static function (TranslationNotFoundEvent $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === 'ru'
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_fires_an_event_when_translation_is_not_found_using_method(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        Event::fake(TranslationNotFoundEvent::class);

        self::assertNull($book->getTranslation('title', 'ru'));

        Event::assertDispatched(TranslationNotFoundEvent::class, static function (TranslationNotFoundEvent $event) use ($book) {
            return $event->attribute === 'title'
                && $event->locale === 'ru'
                && $event->model->is($book);
        });
    }

    /** @test */
    public function it_does_not_fire_translation_not_found_event_when_translation_is_available(): void
    {
        $book = BookFactory::new()->create(['title' => 'My original book']);

        $book->translate('title', 'Моя оригинальная книга', 'ru');

        Event::fake(TranslationNotFoundEvent::class);

        self::assertEquals('Моя оригинальная книга', $book->getTranslation('title', 'ru'));

        Event::assertNotDispatched(TranslationNotFoundEvent::class);
    }
}
