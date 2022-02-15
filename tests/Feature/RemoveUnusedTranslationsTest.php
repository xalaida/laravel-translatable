<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Factories\PostFactory;
use Nevadskiy\Translatable\Tests\Support\Models\Book;
use Nevadskiy\Translatable\Tests\TestCase;

class RemoveUnusedTranslationsTest extends TestCase
{
    /** @test */
    public function it_removes_all_unused_translations_from_the_database(): void
    {
        $book1 = BookFactory::new()->create();
        $book1->translation()->addMany(['title' => 'Птицы', 'description' => 'Книга о птицах'], 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translation()->addMany(['title' => 'Дельфины', 'description' => 'Книга о дельфинах'], 'ru');

        self::assertCount(4, Translation::all());

        Book::where('id', $book1->id)->delete();

        self::assertCount(4, Translation::all());

        $this->artisan('translatable:remove-unused');

        self::assertCount(2, Translation::all());
        self::assertEmpty($book1->translations);
        self::assertCount(2, $book2->translations);
    }

    /** @test */
    public function it_does_not_remove_translations_as_unused_for_soft_deleted_models(): void
    {
        $post1 = PostFactory::new()->create();
        $post1->translation()->add('body', 'Удаленный пост', 'ru');
        $post1->delete();

        $post2 = PostFactory::new()->create();
        $post2->translation()->add('body', 'Тестовый пост', 'ru');

        self::assertCount(2, Translation::all());

        $this->artisan('translatable:remove-unused');

        self::assertCount(2, Translation::all());
    }

    // TODO: feature for additional strategy.
}
