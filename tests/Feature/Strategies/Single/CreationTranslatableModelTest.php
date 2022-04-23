<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

// TODO: add possibility to throw an exception when trying to create model in custom locale
// TODO: add method to check if model can be created in custom locale (useful for UI warning message)
class CreationTranslatableModelTest extends TestCase
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
            $table->text('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_creates_model_without_translations_when_custom_locale_is_set(): void
    {
        $this->app->setLocale('uk');

        $book = new BookForCreation();
        $book->title = 'Hunting smiles';
        $book->save();

        $this->assertDatabaseCount('translations', 0);
        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'title' => 'Hunting smiles',
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
class BookForCreation extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];
}
