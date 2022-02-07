<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies;

use Nevadskiy\Translatable\Tests\Support\Factories\ProductFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class AdditionalTableStrategyTest extends TestCase
{
    /** @test */
    public function it_stores_translation_in_additional_table(): void
    {
        $product = ProductFactory::new()->create(['title' => 'Reindeer Sweater']);

        $product->translation()->set('title', 'Свитер с оленями', 'ru');

        $this->assertDatabaseCount('product_translations', 1);
        $this->assertDatabaseHas('product_translations', [
            'title' => 'Свитер с оленями',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_can_retrieves_translation_from_additional_table(): void
    {
        $product = ProductFactory::new()->create(['title' => 'Reindeer Sweater']);

        $product->translation()->set('title', 'Свитер с оленями', 'ru');

        self::assertEquals('Свитер с оленями', $product->translation()->get('title', 'ru'));
    }
}
