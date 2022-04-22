<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Nevadskiy\Translatable\Behaviours\Single\HasTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class AccessorsTranslationTest extends TestCase
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
            $table->timestamps();
        });
    }

    /** @test */
    public function it_applies_accessors_to_translatable_attributes(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'My article';
        $article->save();

        $article->translation()->add('title', 'моя статья', 'ru');

        self::assertEquals('Моя статья', $article->translation()->get('title', 'ru'));
    }

    /** @test */
    public function it_applies_accessors_to_translatable_attributes_using_getters(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'My article';
        $article->save();

        $this->app->setLocale('ru');

        $article->title = 'моя статья';

        self::assertEquals('Моя статья', $article->title);
    }

    /** @test */
    public function it_still_applies_accessors_to_original_attributes_using_getters(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'my article';
        $article->save();

        self::assertEquals('My article', $article->title);
    }

    /** @test */
    public function it_still_applies_accessors_to_original_attributes_in_fallback_locale(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'my article';
        $article->save();

        $article->translation()->add('title', 'моя статья', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('My article', $article->getOriginalAttribute('title'));
    }

    /** @test */
    public function it_does_not_override_original_attribute_after_applying_accessors(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'my article';
        $article->save();
        $article->translation()->add('title', 'моя статья', 'ru');

        $article->translation()->get('title', 'ru');
        $article->save();

        self::assertEquals('my article', $article->getRawOriginal('title'));
    }

    /** @test */
    public function it_returns_raw_translation_value_for_given_locale(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'my article';
        $article->save();

        $article->translation()->add('title', 'моя статья', 'ru');

        self::assertEquals('моя статья', $article->translation()->raw('title', 'ru'));
    }

    /** @test */
    public function it_correctly_stores_translations_after_applied_accessors(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'my article';
        $article->save();

        $this->app->setLocale('ru');

        $article->title = 'моя статья';
        $article->save();

        self::assertEquals('Моя статья', $article->title);
        $article->save();

        self::assertEquals('моя статья', $article->fresh()->translation()->raw('title', 'ru'));
    }

    /** @test */
    public function it_still_applies_accessors_for_non_translatable_attributes(): void
    {
        $article = new ArticleWithAccessors();
        $article->title = 'my article';
        $article->save();

        $article->translation()->add('description', 'Статья про собак', 'ru');

        $this->app->setLocale('ru');

        self::assertEquals('Ста...', $article->description_short);
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
 * @property string description_short
 */
class ArticleWithAccessors extends Model
{
    use HasTranslations;

    protected $table = 'articles';

    protected $translatable = [
        'title',
        'description'
    ];

    public function getDescriptionShortAttribute(): string
    {
        return Str::limit($this->description, 3);
    }

    public function getTitleAttribute(string $title): string
    {
        return Str::ucfirst($title);
    }
}
