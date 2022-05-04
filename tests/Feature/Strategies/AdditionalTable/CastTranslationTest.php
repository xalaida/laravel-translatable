<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\AdditionalTable;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\AdditionalTable\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

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
        $book->translator()->set('content', [
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ], 'en');
        $book->translator()->set('content', [
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], 'uk');
        $book->save();

        self::assertEquals([
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], $book->translator()->get('content', 'uk'));
    }

    /** @test */
    public function it_casts_translatable_attributes_using_getter(): void
    {
        $book = new BookWithCasts();
        $book->translator()->set('content', [
            'title' => 'Swan flock',
            'author' => 'Vasil Zemlyak',
        ], 'en');
        $book->translator()->set('content', [
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], 'uk');
        $book->save();

        $this->app->setLocale('uk');

        self::assertEquals([
            'title' => 'Лебедина зграя',
            'author' => 'Василь Земляк',
        ], $book->content);
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
        $this->schema()->drop('book_translations');
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

    /**
     * Get the table name of the entity translation.
     */
    protected function getEntityTranslationTable(): string
    {
        return 'book_translations';
    }

    /**
     * Get the foreign key of the entity translation table.
     */
    protected function getEntityTranslationForeignKey(): string
    {
        return 'book_id';
    }
}
