<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
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
        $book->translator()->set('title', 'Melancholic waltz', 'en');
        $book->translator()->set('title', 'Меланхолійний вальс', 'uk');
        $book->save();

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $book->getKey(),
            'translatable_type' => 'books',
            'translatable_attribute' => 'title',
            'locale' => 'en',
            'value' => 'Melancholic waltz',
        ]);
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
