<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Nevadskiy\Translatable\Tests\Support\Factories\PostFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Post;
use Nevadskiy\Translatable\Tests\TestCase;

class RouteBindingByTranslatableAttributeTest extends TestCase
{
    // TODO: add support for resolveChildRouteBinding() method.

    /** @test */
    public function it_resolves_route_binding_by_translatable_attribute(): void
    {
        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
            return $post->id;
        });

        $postAboutGiraffes = PostFactory::new()->create(['slug' => 'post-about-giraffes']);

        $postAboutPenguins = PostFactory::new()->create(['slug' => 'post-about-penguins']);
        $postAboutPenguins->translate('slug', 'пост-о-пингвинах', 'ru');

        $this->app->setLocale('ru');
        $response = $this->get('posts/пост-о-пингвинах');

        $response->assertOk();
        self::assertEquals($postAboutPenguins->id, $response->content());
    }

    /** @test */
    public function it_resolves_route_binding_in_default_locale(): void
    {
        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
            return $post->id;
        });

        $postAboutPenguins = PostFactory::new()->create(['slug' => 'post-about-penguins']);
        $postAboutGiraffes = PostFactory::new()->create(['slug' => 'post-about-giraffes']);

        $response = $this->get('posts/post-about-giraffes');

        $response->assertOk();
        self::assertEquals($postAboutGiraffes->id, $response->content());
    }

    /** @test */
    public function it_still_resolves_route_binding_by_not_translatable_attributes(): void
    {
        Route::middleware('bindings')->get('/posts/{post:id}', static function (Post $post) {
            return $post->id;
        });

        $post1 = PostFactory::new()->create();
        $post2 = PostFactory::new()->create();

        $response = $this->get("posts/{$post2->id}");

        $response->assertOk();
        self::assertEquals($post2->id, $response->content());
    }

    /** @test */
    public function it_resolves_route_binding_model_by_default_value_when_translation_is_not_available(): void
    {
        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
            return $post->id;
        });

        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);

        $this->app->setLocale('ru');
        $response = $this->get('posts/post-about-penguins');

        $response->assertOk();
        self::assertEquals($post->id, $response->content());
    }

    /** @test */
    public function it_returns_404_for_default_value_when_translation_is_available(): void
    {
        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
            return $post->id;
        });

        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
        $post->translate('slug', 'пост-о-пингвинах', 'ru');

        $this->app->setLocale('ru');
        $response = $this->get('posts/post-about-penguins');

        $response->assertNotFound();
    }

    /** @test */
    public function it_returns_404_for_values_in_another_locale(): void
    {
        Route::middleware('bindings')->get('/posts/{post:slug}', static function (Post $post) {
            return $post->id;
        });

        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
        $post->translate('slug', 'пост-о-пингвинах', 'ru');

        $this->app->setLocale('es');
        $response = $this->get('posts/пост-о-пингвинах');

        $response->assertNotFound();
    }

    /** @test */
    public function it_generates_url_using_named_route_by_translatable_attribute(): void
    {
        Route::get('/posts/{post}')->name('posts.show');

        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);
        $post->translate('slug', 'пост-о-пингвинах', 'ru');

        $this->app->setLocale('ru');
        $url = route('posts.show', $post, false);

        self::assertEquals('/posts/'.rawurlencode('пост-о-пингвинах'), $url);
    }

    /** @test */
    public function it_still_generates_url_using_named_route_in_default_locale(): void
    {
        Route::get('/posts/{post}')->name('posts.show');

        $post = PostFactory::new()->create(['slug' => 'post-about-penguins']);

        self::assertEquals('/posts/post-about-penguins', route('posts.show', $post, false));
    }
}
