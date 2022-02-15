<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class TouchParentOnSaveTest extends TestCase
{
    /** @test */
    public function it_updates_parent_updated_at_field_when_translation_is_created(): void
    {
        $createdTime = $this->freezeTime(now()->subMonth());

        $book = BookFactory::new()->create();

        self::assertEquals($createdTime, $book->created_at);

        $translatedTime = $this->freezeTime(now()->addMonth());

        $book->translation()->add('title', 'Переведенное название', 'ru');

        $book = $book->fresh();

        self::assertEquals($createdTime, $book->created_at);
        self::assertEquals($translatedTime, $book->updated_at);
    }
}
