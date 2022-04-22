<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Behaviours\Single\HasTranslations;
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
        $this->schema()->create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_stores_translations_using_morph_map(): void
    {
        Relation::morphMap([
            'articles' => Article::class,
        ]);

        $article = new Article();
        $article->title = 'Book about dolphins';
        $article->save();

        $article->translation()->add('title', 'Книга про дельфинов', 'ru');

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'articles',
        ]);
    }

    /**
     * Tear down the test.
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('articles');

        parent::tearDown();
    }
}

/**
 * @property string title
 */
class Article extends Model
{
    use HasTranslations;

    protected $translatable = [
        'title',
    ];
}
