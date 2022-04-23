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
        $this->schema()->create('articles', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_casts_translatable_attributes(): void
    {
        $article = new ArticleWithCasts();
        $article->content = [
            'title' => 'Chapter 1',
            'body' => 'Chapter about birds',
        ];
        $article->save();

        $article->translator()->add('content', ['title' => 'Глава 1', 'body' => 'Глава о птицах'], 'ru');

        $this->app->setLocale('ru');

        self::assertEquals(['title' => 'Глава 1', 'body' => 'Глава о птицах'], $article->content);
        self::assertCount(1, Translation::all());
    }

    /** @test */
    public function it_casts_attributes_using_fallback_translation(): void
    {
        $article = new ArticleWithCasts();
        $article->content = [
            'title' => 'Chapter 1',
            'body' => 'Chapter about birds',
        ];
        $article->save();

        $this->app->setLocale('ru');

        self::assertEquals(['title' => 'Chapter 1', 'body' => 'Chapter about birds'], $article->content);
    }

    /** @test */
    public function it_still_casts_non_translatable_attributes(): void
    {
        $now = $this->freezeTime(now());

        $article = new ArticleWithCasts();
        $article->content = [
            'title' => 'Chapter 1',
            'body' => 'Chapter about birds',
        ];
        $article->published_at = $now;
        $article->save();

        $this->app->setLocale('ru');

        self::assertInstanceOf(DateTimeInterface::class, $article->published_at);
        self::assertTrue($now->equalTo($article->published_at));
    }

    /**
     * Tear down the test.
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('articles');
        parent::tearDown();
    }
}

/**
 * @property array content
 * @property DateTimeInterface|null published_at
 */
class ArticleWithCasts extends Model
{
    use HasTranslations;

    protected $table = 'articles';

    protected $translatable = [
        'content',
    ];

    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
    ];
}
