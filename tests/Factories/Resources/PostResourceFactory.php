<?php

namespace Magdonia\LaravelFactories\Tests\Factories\Resources;

use Illuminate\Testing\Fluent\AssertableJson;
use Magdonia\LaravelFactories\ResourceFactory;
use Magdonia\LaravelFactories\Tests\Models\Post;

/**
 * @extends ResourceFactory<PostResourceFactory>
 * @property Post $model
 */
class PostResourceFactory extends ResourceFactory
{
    public function definition(AssertableJson $json): void
    {
        $json
            ->where('title', $this->model->title)
            ->where('description', $this->model->description);
    }
}
