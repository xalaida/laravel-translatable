<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\Single\HasTranslations;
use Nevadskiy\Translatable\Strategies\Single\Models\Translation;
use Nevadskiy\Translatable\Tests\TestCase;

class CastTranslationTest extends TestCase
{
    /**
     * Set up the test environment.
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
            'title' => 'Chapter 1',
            'body' => 'Chapter about birds',
        ];
        $book->save();

        $book->translator()->add('content', ['title' => 'Глава 1', 'body' => 'Глава о птицах'], 'ru');

        $this->app->setLocale('ru');

        self::assertEquals(['title' => 'Глава 1', 'body' => 'Глава о птицах'], $book->content);
        self::assertCount(1, Translation::all());
    }

    /** @test */
    public function it_casts_attributes_using_fallback_translation(): void
    {
        $book = new BookWithCasts();
        $book->content = [
            'title' => 'Chapter 1',
            'body' => 'Chapter about birds',
        ];
        $book->save();

        $this->app->setLocale('ru');

        self::assertEquals(['title' => 'Chapter 1', 'body' => 'Chapter about birds'], $book->content);
    }

    /** @test */
    public function it_still_casts_non_translatable_attributes(): void
    {
        $now = $this->freezeTime(now());

        $book = new BookWithCasts();
        $book->content = [
            'title' => 'Chapter 1',
            'body' => 'Chapter about birds',
        ];
        $book->published_at = $now;
        $book->save();

        $this->app->setLocale('ru');

        self::assertInstanceOf(DateTimeInterface::class, $book->published_at);
        self::assertTrue($now->equalTo($book->published_at));
    }

    /**
     * Tear down the test.
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
