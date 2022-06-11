<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
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
            $table->string('title');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_create_model_with_translations(): void
    {
        $book = new BookForCreation();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('description', 'The publication about the inhabitants of our planet', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->translator()->set('description', 'Видання про мешканців нашої планети', 'uk');
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('books', [
            'title' => 'Atlas of animals',
            'description' => 'The publication about the inhabitants of our planet',
        ]);
        $this->assertDatabaseHas('book_translations', [
            'book_id' => $book->getKey(),
            'title' => 'Атлас тварин',
            'description' => 'Видання про мешканців нашої планети',
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
            static::fail('Model was created without fallback translation values.');
        } catch (Exception $e) {
            $this->assertDatabaseCount('books', 0);
            $this->assertDatabaseCount('book_translations', 0);
        }
    }

    /** @test */
    public function it_can_create_model_in_fallback_locale(): void
    {
        $book = new BookForCreation();
        $book->title = 'Atlas of animals';
        $book->description = 'The publication about the inhabitants of our planet';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('book_translations', 0);
        $this->assertDatabaseHas('books', [
            'title' => 'Atlas of animals',
            'description' => 'The publication about the inhabitants of our planet',
        ]);
    }

    /** @test */
    public function it_cannot_create_model_without_translations(): void
    {
        try {
            $book = new BookForCreation();
            $book->save();
            static::fail('Model was created without translations.');
        } catch (Exception $e) {
            $this->assertDatabaseCount('books', 0);
            $this->assertDatabaseCount('book_translations', 0);
        }
    }

    /** @test */
    public function it_stores_model_along_with_translation(): void
    {
        $book = new Book();
        $book->title = 'Ocean monsters';
        $book->translator()->add('title', 'Монстри океану', 'uk');

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('book_translations', 1);
    }

    /** @test */
    public function it_does_not_save_translations_on_second_save_call(): void
    {
        $book = new BookForCreation();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('description', 'The publication about the inhabitants of our planet', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->translator()->set('description', 'Видання про мешканців нашої планети', 'uk');
        $book->save();

        $this->app[ConnectionInterface::class]->enableQueryLog();

        $book->save();

        static::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
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
 * @property string description
 */
class BookForCreation extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
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
