<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\SingleTableExtended;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\SingleTableExtended\HasTranslations;
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

        $this->app->setLocale('uk');

        self::assertEquals([
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], $book->content);
        $this->assertDatabaseCount('translations', 1);
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

        self::assertEquals([
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ], $book->content);
    }

    /** @test */
    public function it_still_casts_non_translatable_attributes(): void
    {
        $now = $this->freezeTime(now());

        $book = new BookWithCasts();
        $book->content = [
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ];
        $book->published_at = $now;
        $book->save();

        $this->app->setLocale('uk');

        self::assertInstanceOf(DateTimeInterface::class, $book->published_at);
        self::assertTrue($now->equalTo($book->published_at));
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
}
