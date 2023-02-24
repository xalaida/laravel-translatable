<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class DeleteTranslatableModelTest extends TestCase
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

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_deletes_translations_along_with_model(): void
    {
        $book = new BookForDeletion();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->translator()->set('title', 'Cienie zapomnianych przodków', 'pl');
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('book_translations', 3);

        $book->delete();

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('book_translations', 0);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('book_translations');
        $this->schema()->drop('books');
        parent::tearDown();
    }
}

/**
 * @property string title
 */
class BookForDeletion extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }
}
