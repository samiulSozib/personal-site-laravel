<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'slug' => fake()->unique()->slug(3),
            'title' => ['en' => $title, 'bn' => $title],
            'excerpt' => ['en' => fake()->sentence(), 'bn' => fake()->sentence()],
            'date' => fake()->date(),
            'category' => 'engineering',
            'tags' => ['Web'],
            'cover' => 'code',
            'published' => true,
            'content' => [
                'en' => [['type' => 'paragraph', 'text' => fake()->paragraph()]],
                'bn' => [['type' => 'paragraph', 'text' => fake()->paragraph()]],
            ],
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['published' => false]);
    }
}
