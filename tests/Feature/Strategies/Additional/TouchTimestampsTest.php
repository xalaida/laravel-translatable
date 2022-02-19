<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Additional;

use Nevadskiy\Translatable\Tests\Support\Factories\ProductFactory;
use Nevadskiy\Translatable\Tests\TestCase;
use function now;

class TouchTimestampsTest extends TestCase
{
    /** @test */
    public function it_updates_timestamps1_when_translation_is_added(): void
    {
        $createdAt = $this->freezeTime(now()->subMonth());

        $product = ProductFactory::new()->create();

        self::assertEquals($createdAt, $product->created_at);

        $translatedAt = $this->freezeTime(now()->addMonth());

        $product->translation()->add('title', 'Переведенное название', 'ru');

        $product = $product->fresh();

        self::assertEquals($createdAt, $product->created_at);
        self::assertEquals($translatedAt, $product->updated_at);
    }
}
