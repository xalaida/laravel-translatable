<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;
use function now;

class TouchTimestampsTest extends TestCase
{
    /** @test */
    public function it_updates_timestamps_when_translation_is_added(): void
    {
        $createdAt = $this->freezeTime(now()->subMonth());

        $book = BookFactory::new()->create();

        self::assertEquals($createdAt, $book->created_at);

        $translatedAt = $this->freezeTime(now()->addMonth());

        $book->translation()->add('title', 'Переведенное название', 'ru');

        $book = $book->fresh();

        self::assertEquals($createdAt, $book->created_at);
        self::assertEquals($translatedAt, $book->updated_at);
    }
}
