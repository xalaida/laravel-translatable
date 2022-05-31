<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTable;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTable\HasTranslations;
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
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->string('title');
            $table->string('description');
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
        $this->assertDatabaseCount('book_translations', 2);
        $this->assertDatabaseHas('book_translations', [
            'book_id' => $book->getKey(),
            'title' => 'Atlas of animals',
            'description' => 'The publication about the inhabitants of our planet',
            'locale' => 'en',
        ]);
        $this->assertDatabaseHas('book_translations', [
            'book_id' => $book->getKey(),
            'title' => 'Атлас тварин',
            'description' => 'Видання про мешканців нашої планети',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_can_create_model_in_custom_locale(): void
    {
        $this->app->setLocale('uk');

        $book = new BookForCreation();
        $book->title = 'Атлас тварин';
        $book->description = 'Видання про мешканців нашої планети';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'book_id' => $book->getKey(),
            'title' => 'Атлас тварин',
            'description' => 'Видання про мешканців нашої планети',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_can_create_model_in_fallback_locale(): void
    {
        $book = new BookForCreation();
        $book->title = 'Atlas of animals';
        $book->description = 'The publication about the inhabitants of our planet';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('book_translations', 1);
        $this->assertDatabaseHas('book_translations', [
            'title' => 'Atlas of animals',
            'description' => 'The publication about the inhabitants of our planet',
            'locale' => $this->app->getFallbackLocale(),
        ]);
    }

    /** @test */
    public function it_can_create_model_without_translations(): void
    {
        $book = new BookForCreation();
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('book_translations', 0);
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

        self::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('books');
        $this->schema()->drop('book_translations');
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
