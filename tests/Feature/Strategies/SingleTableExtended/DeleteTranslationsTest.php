<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class DeleteTranslationsTest extends TestCase
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

//    /** @test */
//    public function it_deletes_translations_along_with_model(): void
//    {
//        $book = new BookForDeleteTranslations();
//        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
//        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
//        $book->translator()->set('title', 'Cienie zapomnianych przodków', 'pl');
//        $book->save();
//
//        $this->assertDatabaseCount('books', 1);
//        $this->assertDatabaseCount('translations', 2);
//
//        // TODO: should delete fallback translations?
//        $book->translator()->delete();
//
//        $this->assertDatabaseCount($book->getTable(), 0);
//        $this->assertDatabaseCount('translations', 0);
//    }

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
class BookForDeleteTranslations extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
