<?php

namespace Magdonia\LaravelFactories\Tests\Factories\Resources;

use Illuminate\Testing\Fluent\AssertableJson;
use Magdonia\LaravelFactories\ResourceFactory;
use Magdonia\LaravelFactories\Tests\Models\User;

/**
 * @extends ResourceFactory<SimpleResourceFactory>
 * @property User $model
 */
class SimpleResourceFactory extends ResourceFactory
{
    public function definition(AssertableJson $json): void
    {
        $json->where('username', $this->model->username);
    }
}
