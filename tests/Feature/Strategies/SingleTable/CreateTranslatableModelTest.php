<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTable;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations;
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
    }

    /** @test */
    public function it_can_create_model_with_translations(): void
    {
        $book = new BookForCreation();
        $book->translator()->set('title', 'Atlas of animals', 'en');
        $book->translator()->set('title', 'Атлас тварин', 'uk');
        $book->save();

        $this->assertDatabaseCount('translations', 2);
        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('translations', [
            'value' => 'Atlas of animals',
            'locale' => 'en',
        ]);
        $this->assertDatabaseHas('translations', [
            'value' => 'Атлас тварин',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_can_create_model_in_custom_locale(): void
    {
        $this->app->setLocale('uk');

        $book = new BookForCreation();
        $book->title = 'Атлас тварин';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseHas('translations', [
            'value' => 'Атлас тварин',
            'locale' => 'uk',
        ]);
    }

    /** @test */
    public function it_can_create_model_in_fallback_locale(): void
    {
        $book = new BookForCreation();
        $book->title = 'Atlas of animals';
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 1);
        $this->assertDatabaseHas('translations', [
            'value' => 'Atlas of animals',
            'locale' => $this->app->getLocale(),
        ]);
    }

    /** @test */
    public function it_can_create_model_without_translations(): void
    {
        $book = new BookForCreation();
        $book->save();

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('translations', 0);
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

        static::assertEmpty($this->app[ConnectionInterface::class]->getQueryLog());
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
