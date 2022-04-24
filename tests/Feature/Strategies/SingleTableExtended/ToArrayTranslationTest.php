<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class ToArrayTranslationTest extends TestCase
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
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_returns_array_with_translations(): void
    {
        $book = new BookToArray();
        $book->title = 'In the jungle';
        $book->description = 'The book is filled with funny funny illustrations and captions';
        $book->save();

        $book->translator()->add('title', 'У джунглях', 'uk');
        $book->translator()->add('description', 'Книга наповнена кумедними яскравими ілюстраціями та підписами до них', 'uk');

        $this->app->setLocale('uk');

        self::assertEquals([
           'title' => 'У джунглях',
           'description' => 'Книга наповнена кумедними яскравими ілюстраціями та підписами до них',
        ], $book->translator()->toArray('uk'));
    }

    /** @test */
    public function it_transforms_model_to_array_using_translatable_values(): void
    {
        $book = new BookToArray();
        $book->title = 'In the jungle';
        $book->description = 'The book is filled with funny funny illustrations and captions';
        $book->save();

        $book->translator()->add('title', 'У джунглях', 'uk');
        $book->translator()->add('description', 'Книга наповнена кумедними яскравими ілюстраціями та підписами до них', 'uk');

        $this->app->setLocale('uk');

        $array = $book->toArray();

        self::assertArrayHasKey('id', $array);
        self::assertArrayHasKey('title', $array);
        self::assertArrayHasKey('description', $array);
        self::assertArrayHasKey('updated_at', $array);
        self::assertArrayHasKey('created_at', $array);
        self::assertEquals('У джунглях', $array['title']);
        self::assertEquals('Книга наповнена кумедними яскравими ілюстраціями та підписами до них', $array['description']);
    }

    /** @test */
    public function it_transforms_to_array_using_model_accessors(): void
    {
        $book = new BookToArray();
        $book->title = 'In the jungle';
        $book->save();

        $book->translator()->add('title', 'у джунглях', 'uk');

        $this->app->setLocale('uk');

        $array = $book->toArray();

        self::assertEquals('У джунглях', $array['title']);
        self::assertNull($array['description']);
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
 * @property string|null description
 */
class BookToArray extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'title',
        'description',
    ];

    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }
}
