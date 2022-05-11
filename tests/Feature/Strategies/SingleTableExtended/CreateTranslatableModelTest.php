<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class CreateTranslatableModelTest extends TestCase
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
    public function it_can_be_created_with_multiple_translations_at_once(): void
    {
        $book = new BookForCreation();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'title' => 'Atlas of animals',
        ]);
        $this->assertDatabaseHas('translations', [
            'value' => 'Атлас тварин',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_cannot_be_created_when_no_translation_was_set_to_fallback_locale(): void
    {
        $book = new BookForCreation();
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->translator()->set('title', 'Atlas zwierząt', 'pl');

        try {
            $book->save();
            $this->fail("Model was created without fallback translation values.");
        } catch (Exception $e) {
            $this->assertDatabaseCount('books', 0);
            $this->assertDatabaseCount('translations', 0);
        }
    }

    /** @test */
    public function it_cannot_be_created_in_custom_locale_when_attribute_is_not_nullable(): void
    {
        $this->app->setLocale('uk');

        $book = new BookForCreation();
        $book->title = 'Hunting smiles';

        try {
            $book->save();
            $this->fail("Model was created without fallback translation values.");
        } catch (Exception $e) {
            $this->assertDatabaseCount('books', 0);
            $this->assertDatabaseCount('translations', 0);
        }
    }

    /** @test */
    public function it_stores_model_along_with_translation(): void
    {
        $book = new Book();
        $book->title = 'Ocean monsters';
        $book->translator()->add('title', 'Монстри океану', 'uk');

        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseCount('books', 1);
    }

    /** @test */
    public function it_does_not_save_translations_on_second_save_call(): void
    {
        $book = new BookForCreation();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        $book->save();

        self::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
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
