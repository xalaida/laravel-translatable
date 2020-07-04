<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Nevadskiy\Translatable\Events\TranslationSavedEvent;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class FireTranslationSavedTest extends TestCase
{
    /** @test */
    public function it_fires_an_event_when_translation_has_been_saved_attribute(): void
    {
        $book = BookFactory::new()->create();

        Event::fake(TranslationSavedEvent::class);

        $book->translate('title', 'Моя книга', 'ru');

        Event::assertDispatched(TranslationSavedEvent::class, function (TranslationSavedEvent $event) use ($book) {
            return $event->translation->translatable->is($book)
                && $event->translation->translatable_attribute === 'title'
                && $event->translation->locale === 'ru'
                && $event->translation->value === 'Моя книга';
        });
    }

    /** @test */
    public function it_does_not_fire_translation_saved_event_when_translatable_model_is_just_created(): void
    {
        Event::fake(TranslationSavedEvent::class);

        BookFactory::new()->create();

        Event::assertNotDispatched(TranslationSavedEvent::class);
    }
}
