<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class DeleteTranslatableModelTestTest extends TestCase
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

        $this->schema()->create('archival_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /** @test */
    public function it_deletes_translations_along_with_model(): void
    {
        $book = new BookToDelete();
        $book->title = 'Shadows of Forgotten Ancestors';
        $book->save();

        $book->translator()->add('title', 'Тіні забутих предків', 'uk');
        $book->translator()->add('title', 'Cienie zapomnianych przodków', 'pl');

        $this->assertDatabaseCount($book->getTable(), 1);
        $this->assertDatabaseCount('translations', 2);

        $book->delete();

        $this->assertDatabaseCount($book->getTable(), 0);
        $this->assertDatabaseCount('translations', 0);
    }

    /** @test */
    public function it_does_not_delete_translations_when_model_is_soft_deleted(): void
    {
        $book = new BookWithSoftDelete();
        $book->title = 'Shadows of Forgotten Ancestors';
        $book->save();

        $book->translator()->add('title', 'Тіні забутих предків', 'uk');

        $this->assertDatabaseCount($book->getTable(), 1);
        $this->assertDatabaseCount('translations', 1);

        $book->delete();

        $this->assertSoftDeleted($book);
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_deletes_translations_of_force_deleted_models(): void
    {
        $book = new BookWithSoftDelete();
        $book->title = 'Shadows of Forgotten Ancestors';
        $book->save();

        $book->translator()->add('title', 'Тіні забутих предків', 'uk');

        $this->assertDatabaseCount($book->getTable(), 1);
        $this->assertDatabaseCount('translations', 1);

        $book->forceDelete();

        $this->assertDatabaseCount($book->getTable(), 0);
        $this->assertDatabaseCount('translations', 0);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('books');
        $this->schema()->drop('archival_books');
        parent::tearDown();
    }
}

/**
 * @property string title
 */
class BookToDelete extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}

/**
 * @property string title
 */
class BookWithSoftDelete extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected $table = 'archival_books';

    protected $translatable = [
        'title',
    ];
}
