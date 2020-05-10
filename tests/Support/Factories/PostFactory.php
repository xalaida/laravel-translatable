<?php

namespace Nevadskiy\Translatable\Tests\Support\Factories;

use Nevadskiy\Translatable\Tests\Support\Models\Post;

class PostFactory
{
    /**
     * Static constructor.
     *
     * @return static
     */
    public static function new(): self
    {
        return new static;
    }

    /**
     * Create a new post instance and save it into the database.
     *
     * @param array $attributes
     * @return Post
     */
    public function create(array $attributes = []): Post
    {
        $post = new Post(array_merge($this->getDefaults(), $attributes));
        $post->save();

        return $post;
    }

    /**
     * Get default values.
     *
     * @return array
     */
    private function getDefaults(): array
    {
        return [
            'body' => 'Testing post body',
        ];
    }
}
