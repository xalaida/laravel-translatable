<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\Relation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class MorphMapTest extends TestCase
{
    /** @test */
    public function it_can_use_morph_map_as_normal_for_storing_translations(): void
    {
        Relation::morphMap([
            'books' => Book::class,
        ]);

        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);
        $book->translate('title', 'Книга про дельфинов', 'ru');

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'books',
        ]);
        $this->assertEquals('Книга про дельфинов', $book->getTranslation('title', 'ru'));
    }
}
