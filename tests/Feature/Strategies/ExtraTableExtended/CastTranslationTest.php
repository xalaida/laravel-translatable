<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\ExtraTableExtended;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

/**
 * TODO: test it using in-memory array strategy and simplify database test to just test attributes and originals
 */
class CastTranslationTest extends TestCase
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
            $table->text('content');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('book_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id');
            $table->text('content')->nullable();
            $table->string('locale');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_casts_translatable_attributes(): void
    {
        $book = new BookWithCasts();
        $book->content = [
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ];
        $book->save();

        $book->translator()->add('content', [
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], 'uk');

        static::assertSame([
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], $book->translator()->get('content', 'uk'));
        $this->assertDatabaseCount('book_translations', 1);
    }

    /** @test */
    public function it_casts_translatable_attributes_using_attribute_interceptor(): void
    {
        $book = new BookWithCasts();
        $book->content = [
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ];
        $book->save();

        $book->translator()->add('content', [
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], 'uk');

        $this->app->setLocale('uk');

        static::assertSame([
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], $book->content);
        $this->assertDatabaseCount('book_translations', 1);
    }

    /** @test */
    public function it_casts_attributes_using_fallback_locale(): void
    {
        $book = new BookWithCasts();
        $book->content = [
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ];
        $book->save();

        $this->app->setLocale('uk');

        static::assertSame([
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ], $book->content);
    }

    /** @test */
    public function it_still_casts_non_translatable_attributes(): void
    {
        Carbon::setTestNow(now()->startOfSecond());

        $book = new BookWithCasts();
        $book->content = [
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ];
        $book->published_at = now();
        $book->save();

        $this->app->setLocale('uk');

        static::assertInstanceOf(DateTimeInterface::class, $book->published_at);
        static::assertTrue(now()->equalTo($book->published_at));
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
 * @property array content
 * @property DateTimeInterface|null published_at
 */
class BookWithCasts extends Model
{
    use HasTranslations;

    protected $table = 'books';

    protected $translatable = [
        'content',
    ];

    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
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
