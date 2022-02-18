<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\HasEntityTranslations;
use Nevadskiy\Translatable\Strategies\AdditionalTableStrategy;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use Nevadskiy\Translatable\Tests\TestCase;

class AdditionalTableStrategyExtendingTest extends TestCase
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
            $table->timestamps();
        });

        $this->schema()->create('article_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('article_id')->constrained();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('locale', 2);
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_store_model_correctly_in_extending_translatable_attributes_mode_using_fallback_locale(): void
    {
        $article = new Article();
        $article->title = 'Article about parrots';
        $article->description = 'How to teach a parrot to talk';
        $article->save();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', [
            'id' => $article->getKey()
        ]);

        $this->assertDatabaseCount('article_translations', 1);
        $this->assertDatabaseHas('article_translations', [
            'article_id' => $article->getKey(),
            'title' => 'Article about parrots',
            'description' => 'How to teach a parrot to talk',
            'locale' => 'en',
        ]);
    }

    /** @test */
    public function it_can_store_model_correctly_in_extending_translatable_attributes_mode_using_custom_locale(): void
    {
        $this->app->setLocale('ru');

        $article = new Article();
        $article->title = 'Статья о попугаях';
        $article->description = 'Как научить разговаривать попугая';
        $article->save();

        $this->assertDatabaseCount('articles', 1);
        $this->assertDatabaseHas('articles', [
            'id' => $article->getKey()
        ]);

        $this->assertDatabaseCount('article_translations', 1);
        $this->assertDatabaseHas('article_translations', [
            'article_id' => $article->getKey(),
            'title' => 'Статья о попугаях',
            'description' => 'Как научить разговаривать попугая',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_can_handle_translations_using_extending_translatable_attributes_mode(): void
    {
        $article = new Article();
        $article->save();

        $article->translation()->set('title', 'Статья о попугаях', 'ru');
        $article->translation()->set('description', 'Как научить разговаривать попугая', 'ru');
        $article->translation()->save();

        $this->assertDatabaseCount('article_translations', 1);
        $this->assertDatabaseHas('article_translations', [
            'article_id' => $article->getKey(),
            'title' => 'Статья о попугаях',
            'description' => 'Как научить разговаривать попугая',
            'locale' => 'ru',
        ]);
    }

    /** @test */
    public function it_can_handle_translations_with_fallback_locale_using_extending_translatable_attributes_mode(): void
    {
        $article = new Article();
        $article->save();

        $article->translation()->set('title', 'Article about parrots', 'en');
        $article->translation()->set('description', 'How to teach a parrot to talk', 'en');
        $article->translation()->save();

        $this->assertDatabaseCount('article_translations', 1);
        $this->assertDatabaseHas('article_translations', [
            'article_id' => $article->getKey(),
            'title' => 'Article about parrots',
            'description' => 'How to teach a parrot to talk',
            'locale' => 'en',
        ]);
    }

    /**
     * Tear down the test.
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('articles');
        $this->schema()->drop('article_translations');

        parent::tearDown();
    }
}

/**
 * @property string title
 * @property string description
 */
class Article extends Model
{
    use HasEntityTranslations;

    protected $translatable = [
        'title',
        'description'
    ];

    protected function getTranslationStrategy(): TranslatorStrategy
    {
        return (new AdditionalTableStrategy($this))
            ->extendingTranslatableStructure();
    }
}