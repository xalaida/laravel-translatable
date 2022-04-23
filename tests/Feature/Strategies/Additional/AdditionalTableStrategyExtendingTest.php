<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Additional;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Nevadskiy\Translatable\Strategies\Entity\AdditionalTableStrategy;
use Nevadskiy\Translatable\Strategies\Entity\HasTranslations;
use Nevadskiy\Translatable\Strategies\TranslatorStrategy;
use Nevadskiy\Translatable\Tests\TestCase;

class AdditionalTableStrategyExtendingTest extends TestCase
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

        $article->translator()->set('title', 'Статья о попугаях', 'ru');
        $article->translator()->set('description', 'Как научить разговаривать попугая', 'ru');
        $article->translator()->save();

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

        $article->translator()->set('title', 'Article about parrots', 'en');
        $article->translator()->set('description', 'How to teach a parrot to talk', 'en');
        $article->translator()->save();

        $this->assertDatabaseCount('article_translations', 1);
        $this->assertDatabaseHas('article_translations', [
            'article_id' => $article->getKey(),
            'title' => 'Article about parrots',
            'description' => 'How to teach a parrot to talk',
            'locale' => 'en',
        ]);
    }

    /**
     * @inheritdoc
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
    use HasTranslations;

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
