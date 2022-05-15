<?php

namespace Magdonia\LaravelFactories\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Magdonia\LaravelFactories\Tests\Models\Post;
use Magdonia\LaravelFactories\Tests\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->optional()->sentence(),
            'creator_id' => User::factory(),
        ];
    }
}
