<?php

namespace Nevadskiy\Translatable\Tests\Feature;

use Nevadskiy\Translatable\Models\Translation;
use Nevadskiy\Translatable\Tests\Support\Factories\BookFactory;
use Nevadskiy\Translatable\Tests\Support\Factories\PostFactory;
use Nevadskiy\Translatable\Tests\TestCase;

class RemoveUnusedTranslationsTest extends TestCase
{
    /** @test */
    public function it_removes_all_unused_translations_from_the_database(): void
    {
        $book1 = BookFactory::new()->create();
        $removedTranslations = $book1->translateMany(['title' => 'Птицы', 'description' => 'Книга о птицах'], 'ru');

        $book2 = BookFactory::new()->create();
        $translations = $book2->translateMany(['title' => 'Дельфины', 'description' => 'Книга о дельфинах'], 'ru');

        $book1->delete();

        $this->assertCount(4, Translation::all());

        $this->artisan('translatable:remove-unused');

        $this->assertCount(2, Translation::all());

        $this->assertNotNull($translations[0]->fresh());
        $this->assertNotNull($translations[1]->fresh());

        $this->assertNull($removedTranslations[0]->fresh());
        $this->assertNull($removedTranslations[1]->fresh());
    }

    /** @test */
    public function it_does_not_remove_translations_as_unused_for_soft_deleted_models(): void
    {
        $post1 = PostFactory::new()->create();
        $post1->translate('body', 'Удаленный пост', 'ru');
        $post1->delete();

        $post2 = PostFactory::new()->create();
        $post2->translate('body', 'Тестовый пост', 'ru');

        $this->assertCount(2, Translation::all());

        $this->artisan('translatable:remove-unused');

        $this->assertCount(2, Translation::all());
    }
}
