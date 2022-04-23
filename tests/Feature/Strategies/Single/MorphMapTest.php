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
     * @inheritdoc
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
            'books' => BookWithMorphMap::class,
        ]);

        $book = new BookWithMorphMap();
        $book->title = 'Melancholic waltz';
        $book->save();

        $book->translator()->add('title', 'Меланхолійний вальс', 'uk');

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->getKey(),
            'translatable_type' => 'books',
            'translatable_attribute' => 'title',
            'locale' => 'uk',
            'value' => 'Меланхолійний вальс',
        ]);
    }

    /**
     * @inheritdoc
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
class BookWithMorphMap extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
