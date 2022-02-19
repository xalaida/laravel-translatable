<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Additional;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Nevadskiy\Translatable\HasEntityTranslations;
use Nevadskiy\Translatable\Tests\TestCase;

class RouteBindingByTranslatableAttributeTest extends TestCase
{
    // TODO: add support for resolveChildRouteBinding() method.

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
            $table->string('slug');
            $table->timestamps();
        });

        $this->schema()->create('article_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('article_id')->constrained();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('locale', 2);
            $table->timestamps();
        });
    }

    /** @test */
    public function it_resolves_route_model_binding_by_translatable_attribute(): void
    {
        Route::middleware('bindings')->get('/articles/{article:slug}', function (ArticleWithSlug $article) {
            return $article->getKey();
        });

        $article = new ArticleWithSlug();
        $article->title = 'Article about penguins';
        $article->slug = 'article-about-penguins';
        $article->save();

        $article->translation()->add('slug', 'статья-о-пингвинах', 'ru');

        $this->app->setLocale('ru');

        $response = $this->get('/articles/статья-о-пингвинах');

        $response->assertOk();
        self::assertEquals($article->getKey(), $response->content());
    }

//    /** @test */
//    public function it_resolves_route_binding_by_translatable_attribute(): void
//    {
//        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
//            return $post->id;
//        });
//
//        $postAboutGiraffes = PostFactory::new()->create(['slug' => 'post-about-giraffes']);
//
//        $postAboutPenguins = PostFactory::new()->create(['slug' => 'post-about-penguins']);
//        $postAboutPenguins->translation()->add('slug', 'пост-о-пингвинах', 'ru');
//
//        $this->app->setLocale('ru');
//        $response = $this->get('posts/пост-о-пингвинах');
//
//        $response->assertOk();
//        self::assertEquals($postAboutPenguins->id, $response->content());
//    }
//
//    /** @test */
//    public function it_resolves_route_binding_in_default_locale(): void
//    {
//        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
//            return $post->id;
//        });
//
//        $postAboutPenguins = PostFactory::new()->create(['slug' => 'post-about-penguins']);
//        $postAboutGiraffes = PostFactory::new()->create(['slug' => 'post-about-giraffes']);
//
//        $response = $this->get('posts/post-about-giraffes');
//
//        $response->assertOk();
//        self::assertEquals($postAboutGiraffes->id, $response->content());
//    }
//
//    /** @test */
//    public function it_still_resolves_route_binding_by_non_translatable_attributes(): void
//    {
//        Route::middleware('bindings')->get('/posts/{post:id}', static function (Post $post) {
//            return $post->id;
//        });
//
//        $post1 = PostFactory::new()->create();
//        $post2 = PostFactory::new()->create();
//
//        $response = $this->get("posts/{$post2->id}");
//
//        $response->assertOk();
//        self::assertEquals($post2->id, $response->content());
//    }
//
//    /** @test */
//    public function it_resolves_route_binding_model_by_default_value_when_translation_is_not_available(): void
//    {
//        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
//            return $post->id;
//        });
//
//        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
//
//        $this->app->setLocale('ru');
//        $response = $this->get('posts/post-about-penguins');
//
//        $response->assertOk();
//        self::assertEquals($post->id, $response->content());
//    }
//
//    /** @test */
//    public function it_returns_404_for_default_value_when_translation_is_available(): void
//    {
//        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
//            return $post->id;
//        });
//
//        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
//        $post->translation()->add('slug', 'пост-о-пингвинах', 'ru');
//
//        $this->app->setLocale('ru');
//        $response = $this->get('posts/post-about-penguins');
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function it_returns_404_for_values_in_another_locale(): void
//    {
//        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
//            return $post->id;
//        });
//
//        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
//        $post->translation()->add('slug', 'пост-о-пингвинах', 'ru');
//
//        $this->app->setLocale('es');
//        $response = $this->get('posts/пост-о-пингвинах');
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function it_generates_url_using_named_route_by_translatable_attribute(): void
//    {
//        Route::get('/posts/{post}')->name('posts.show');
//
//        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
//        $post->translation()->add('slug', 'пост-о-пингвинах', 'ru');
//
//        $this->app->setLocale('ru');
//        $url = route('posts.show', $post, false);
//
//        self::assertEquals('/posts/'.rawurlencode('пост-о-пингвинах'), $url);
//    }
//
//    /** @test */
//    public function it_still_generates_url_using_named_route_in_default_locale(): void
//    {
//        Route::get('/posts/{post}')->name('posts.show');
//
//        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
//
//        self::assertEquals('/posts/post-about-penguins', route('posts.show', $post, false));
//    }
}

/**
 * @property string title
 * @property string slug
 */
class ArticleWithSlug extends Model
{
    use HasEntityTranslations;

    protected $table = 'articles';

    protected $translatable = [
        'title',
        'slug',
    ];

    /**
     * Get the table name of the entity translation.
     */
    protected function getEntityTranslationTable(): string
    {
        return 'article_translations';
    }

    /**
     * Get the foreign key of the entity translation table.
     */
    protected function getEntityTranslationForeignKey(): string
    {
        return 'article_id';
    }
}
