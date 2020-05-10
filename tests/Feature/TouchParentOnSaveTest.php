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

        $this->assertEquals($createdTime, $book->created_at);

        $translatedTime = $this->freezeTime(now()->addMonth());

        $book->translate('title', 'Переведенное название', 'ru');

        $book = $book->fresh();

        $this->assertEquals($createdTime, $book->created_at);
        $this->assertEquals($translatedTime, $book->updated_at);
    }
}
