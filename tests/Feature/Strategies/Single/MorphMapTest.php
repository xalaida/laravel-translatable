<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class MorphMapTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    /**
     * Set up the database schema.
     */
    private function createSchema(): void
    {
        $this->schema()->create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_stores_translations_using_morph_map(): void
    {
        Relation::morphMap([
            'books' => BookForMorphMap::class,
        ]);

        $book = new BookForMorphMap();
        $book->title = 'Book about dolphins';
        $book->save();

        $book->translator()->add('title', 'Книга про дельфінів', 'uk');

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->getKey(),
            'translatable_type' => 'books',
            'translatable_attribute' => 'title',
            'locale' => 'uk',
            'value' => 'Книга про дельфінів',
        ]);
    }

    /**
     * Tear down the test.
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string title
 */
class BookForMorphMap extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
