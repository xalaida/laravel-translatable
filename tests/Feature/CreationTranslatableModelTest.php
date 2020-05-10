<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class CreationTranslatableModelTest extends TestCase
{
    /** @test */
    public function it_creates_model_in_non_default_without_translations(): void
    {
        $this->app->setLocale('ru');

        $book = new Book([
            'title' => 'My book',
            'description' => 'Book about birds',
            'version' => '1',
        ]);
        $book->save();

        $this->assertEmpty(Translation::all());
        $this->assertDatabaseHas($book->getTable(), [
            'title' => 'My book',
            'description' => 'Book about birds',
            'version' => '1',
        ]);
    }
}
