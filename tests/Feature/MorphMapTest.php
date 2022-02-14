<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\Relation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class MorphMapTest extends TestCase
{
    /** @test */
    public function it_stores_translations_using_morph_map(): void
    {
        Relation::morphMap([
            'books' => Book::class,
        ]);

        $book = BookFactory::new()->create(['title' => 'Book about dolphins']);
        $book->translation()->add('title', 'Книга про дельфинов', 'ru');

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'books',
        ]);
        self::assertEquals('Книга про дельфинов', $book->getTranslation('title', 'ru'));
    }
}
