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
            $table->string('title');
            $table->string('description');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_mutators_for_translatable_attributes(): void
    {
        $article = new ArticleWithMutators();
        $article->title = 'My article';
        $article->description = 'My first article';
        $article->save();

        $article->translation()->add('title', 'Очень очень длинное название для статьи', 'ru');

        $this->assertDatabaseHas('translations', [
            'value' => 'Очень очень длинное название д...',
        ]);
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_mutators(): void
    {
        $article = new ArticleWithMutators();
        $article->title = 'My article';
        $article->description = 'My first article';
        $article->save();

        $article->translation()->add('title', 'Очень очень длинное название для книги', 'ru');

        self::assertEquals('My article', $article->title);
    }

    /** @test */
    public function it_applies_mutators_using_setter(): void
    {
        $article = new ArticleWithMutators();
        $article->title = 'My article';
        $article->description = 'My first article';
        $article->save();

        $this->app->setLocale('ru');

        $article->title = 'Очень очень длинное название для статьи';

        self::assertEquals('Очень очень длинное название д...', $article->title);
    }

    /** @test */
    public function it_still_applies_mutators_for_non_translatable_attributes(): void
    {
        $article = new ArticleWithMutators();
        $article->title = 'My article';
        $article->description = 'Very long description for the article';
        $article->save();

        self::assertEquals('Very long description for the...', $article->description);
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
 * @property string title
 * @property string description
 */
class ArticleWithMutators extends Model
{
    use HasTranslations;

    protected $table = 'articles';

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
