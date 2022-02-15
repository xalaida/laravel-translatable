<?php

namespace Nevadskiy\Translatable\Tests\Feature\SingleTableStrategy;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class CreationTranslatableModelTest extends TestCase
{
    /** @test */
    public function it_creates_model_in_non_default_without_translations(): void
    {
        $this->app->setLocale('ru');

        $book = BookFactory::new()->create([
            'title' => 'My book',
            'description' => 'Book about birds',
            'version' => '1',
        ]);

        self::assertEmpty(Translation::all());
        $this->assertDatabaseHas($book->getTable(), [
            'title' => 'My book',
            'description' => 'Book about birds',
            'version' => '1',
        ]);
    }
}
