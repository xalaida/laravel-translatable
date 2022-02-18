<?php

namespace Nevadskiy\Translatable\Tests\Feature\Strategies\Single;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Factories\PostFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class RemoveTranslationsOfDeletedModelTest extends TestCase
{
    /** @test */
    public function it_removes_translations_when_model_is_deleted(): void
    {
        $book1 = BookFactory::new()->create();
        $book1->translation()->addMany(['title' => 'Птицы', 'description' => 'Книга про птиц'], 'ru');

        $book2 = BookFactory::new()->create();
        $book2->translation()->addMany(['title' => 'Дельфины', 'description' => 'Книга о дельфинах'], 'ru');

        self::assertCount(4, Translation::all());

        $book1->delete();

        self::assertEmpty($book1->translations);
        self::assertCount(2, $book2->translations);
    }

    /** @test */
    public function it_does_not_remove_translations_when_model_is_soft_deleted(): void
    {
        $post1 = PostFactory::new()->create();
        $post1->translation()->add('body', 'Удаленный пост', 'ru');

        self::assertCount(1, Translation::all());

        $post1->delete();

        self::assertCount(1, Translation::all());
    }

    /** @test */
    public function it_removes_translations_of_force_deleted_models(): void
    {
        $post1 = PostFactory::new()->create();
        $post1->translation()->add('body', 'Удаленный пост', 'ru');

        self::assertCount(1, Translation::all());

        $post1->forceDelete();

        self::assertCount(0, Translation::all());
    }
}
