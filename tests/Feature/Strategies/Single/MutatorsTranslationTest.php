<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class MutatorsTranslationTest extends TestCase
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
            $table->string('description');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_mutators_for_translatable_attributes(): void
    {
        $book = new BookWithMutators();
        $book->title = 'My book';
        $book->description = 'My first book';
        $book->save();

        $book->translation()->add('title', 'Очень очень длинное название для статьи', 'ru');

        $this->assertDatabaseHas('translations', [
            'value' => 'Очень очень длинное название д...',
        ]);
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_mutators(): void
    {
        $book = new BookWithMutators();
        $book->title = 'My book';
        $book->description = 'My first book';
        $book->save();

        $book->translation()->add('title', 'Очень очень длинное название для книги', 'ru');

        self::assertEquals('My book', $book->title);
    }

    /** @test */
    public function it_applies_mutators_using_setter(): void
    {
        $book = new BookWithMutators();
        $book->title = 'My book';
        $book->description = 'My first book';
        $book->save();

        $this->app->setLocale('ru');

        $book->title = 'Очень очень длинное название для статьи';

        self::assertEquals('Очень очень длинное название д...', $book->title);
    }

    /** @test */
    public function it_still_applies_mutators_for_non_translatable_attributes(): void
    {
        $book = new BookWithMutators();
        $book->title = 'My book';
        $book->description = 'Very long description for the book';
        $book->save();

        self::assertEquals('Very long description for the...', $book->description);
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
 * @property string description
 */
class BookWithMutators extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
    ];

    public function setTitleAttribute(string $title): void
    {
        $this->attributes['title'] = Str::limit($title, 30);
    }

    public function setDescriptionAttribute(string $description): void
    {
        $this->attributes['description'] = Str::limit($description, 30);
    }
}
