<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Additional;

use Illuminate\Support\Facades\DB;
use Nevadskiy\Translatable\Tests\Support\Factories\ProductFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Product;
use Nevadskiy\Translatable\Tests\TestCase;

class AdditionalTableStrategyTest extends TestCase
{
    /** @test */
    public function it_can_create_models_in_custom_locale_correctly(): void
    {
        $this->app->setLocale('ru');

        $product = new Product();
        $product->title = 'Свитер с оленями';
        $product->description = 'Теплый зимний свитер';
        $product->save();

        $this->assertDatabaseHas('products', [
            'title' => 'Свитер с оленями',
            'description' => 'Теплый зимний свитер',
        ]);
        $this->assertDatabaseCount('product_translations', 0);
    }

    /** @test */
    public function it_stores_translatable_model_correctly(): void
    {
        ProductFactory::new()->create([
            'title' => 'Reindeer Sweater',
            'description' => 'Warm winter sweater',
        ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Reindeer Sweater',
            'description' => 'Warm winter sweater',
        ]);
        $this->assertDatabaseCount('product_translations', 0);
    }

    /** @test */
    public function it_stores_translations_in_additional_table(): void
    {
        $product = ProductFactory::new()->create([
            'title' => 'Reindeer Sweater',
            'description' => 'Warm winter sweater',
        ]);

        $product->translator()->set('title', 'Свитер с оленями', 'ru');
        $product->translator()->set('description', 'Теплый зимний свитер', 'ru');
        $product->translator()->save();

        $this->assertDatabaseCount('product_translations', 1);
        $this->assertDatabaseHas('product_translations', [
            'title' => 'Свитер с оленями',
            'description' => 'Теплый зимний свитер',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_does_not_store_translations_without_save_call(): void
    {
        $product = ProductFactory::new()->create([
            'title' => 'Reindeer Sweater',
            'description' => 'Warm winter sweater',
        ]);

        $product->translator()->set('title', 'Свитер с оленями', 'ru');
        $product->translator()->set('description', 'Теплый зимний свитер', 'ru');

        $this->assertDatabaseCount('product_translations', 0);
    }

    /** @test */
    public function it_does_not_override_translations_on_double_save_call(): void
    {
        $product = ProductFactory::new()->create([
            'title' => 'Reindeer Sweater',
            'description' => 'Warm winter sweater',
        ]);

        $product->translator()->set('title', 'Свитер с оленями', 'ru');
        $product->translator()->set('description', 'Теплый зимний свитер', 'ru');
        $product->translator()->save();

        DB::enableQueryLog();

        $product->translator()->save();

        self::assertEmpty(DB::connection()->getQueryLog());
        $this->assertDatabaseCount('product_translations', 1);
    }

    /** @test */
    public function it_retrieves_translation_from_additional_table(): void
    {
        $product = ProductFactory::new()->create(['title' => 'Reindeer Sweater']);

        $product->translator()->set('title', 'Свитер с оленями', 'ru');

        self::assertEquals('Свитер с оленями', $product->translator()->get('title', 'ru'));
    }

    /** @test */
    public function it_automatically_stores_translation_for_translatable_attribute_using_current_locale(): void
    {
        $product = ProductFactory::new()->create(['title' => 'Reindeer Sweater']);

        $this->app->setLocale('ru');

        $product->title = 'Свитер с оленями';
        $product->save();

        $this->assertDatabaseCount('product_translations', 1);
        $this->assertDatabaseHas('product_translations', [
            'title' => 'Свитер с оленями',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_automatically_retrieves_translation_for_translatable_attribute_using_current_locale(): void
    {
        $product = ProductFactory::new()->create(['title' => 'Reindeer Sweater']);

        $product->translator()->set('title', 'Свитер с оленями', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Свитер с оленями', $product->title);
    }

    /** @test */
    public function it_retrieves_translations_without_additional_queries_when_they_are_preloaded(): void
    {
        $product1 = ProductFactory::new()->create(['title' => 'Reindeer Sweater']);
        $product2 = ProductFactory::new()->create(['title' => 'Sony PlayStation']);
        $product3 = ProductFactory::new()->create(['title' => 'LG Boiler']);

        $product1->translator()->add('title', 'Свитер с оленями', 'ru');
        $product2->translator()->add('title', 'Sony ИгроваяСтанция', 'ru');
        $product3->translator()->add('title', 'LG чайник', 'ru');

        $products = Product::query()->withoutGlobalScopes()->with('translations')->get();

        $this->app->setLocale('ru');

        DB::enableQueryLog();

        self::assertEquals('Свитер с оленями', $products[0]->title);
        self::assertEquals('Sony ИгроваяСтанция', $products[1]->title);
        self::assertEquals('LG чайник', $products[2]->title);

        self::assertEmpty(DB::getQueryLog());
    }

    /** @test */
    public function it_automatically_eager_loads_translations_for_current_locale(): void
    {
        $product1 = ProductFactory::new()->create(['title' => 'Reindeer Sweater']);
        $product2 = ProductFactory::new()->create(['title' => 'Sony PlayStation']);
        $product3 = ProductFactory::new()->create(['title' => 'LG Boiler']);

        $product1->translator()->add('title', 'Свитер с оленями', 'ru');
        $product2->translator()->add('title', 'Sony ИгроваяСтанция', 'ru');
        $product3->translator()->add('title', 'LG чайник', 'ru');

        $this->app->setLocale('ru');

        $products = Product::query()->get();

        DB::enableQueryLog();

        self::assertEquals('Свитер с оленями', $products[0]->title);
        self::assertEquals('Sony ИгроваяСтанция', $products[1]->title);
        self::assertEquals('LG чайник', $products[2]->title);

        self::assertEmpty(DB::getQueryLog());
    }
}
