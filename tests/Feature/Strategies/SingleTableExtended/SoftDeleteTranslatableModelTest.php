<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class SoftDeleteTranslatableModelTest extends TestCase
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
            $table->softDeletes();
        });
    }

    /** @test */
    public function it_does_not_delete_translations_when_model_is_soft_deleted(): void
    {
        $book = new BookWithSoftDelete();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 1);

        $book->delete();

        $this->assertSoftDeleted($book);
        $this->assertDatabaseCount('translations', 1);
    }

    /** @test */
    public function it_deletes_translations_of_force_deleted_models(): void
    {
        $book = new BookWithSoftDelete();
        $book->translator()->set('title', 'Shadows of Forgotten Ancestors', 'en');
        $book->translator()->set('title', 'Тіні забутих предків', 'uk');
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 1);

        $book->forceDelete();

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('translations', 0);
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
class BookWithSoftDelete extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
